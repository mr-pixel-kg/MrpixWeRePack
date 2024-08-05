<?php

namespace Mrpix\WeRepack\Subscriber;

use Mrpix\WeRepack\Components\WeRepackSession;
use Mrpix\WeRepack\Core\Content\RepackOrder\RepackOrderEntity;
use Mrpix\WeRepack\Repository\SalesChannelRepository;
use Mrpix\WeRepack\Service\ConfigService;
use Mrpix\WeRepack\Service\MailService;
use Mrpix\WeRepack\Service\OrderService;
use Mrpix\WeRepack\Service\PromotionService;
use Mrpix\WeRepack\Service\WeRepackTelemetryService;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\System\SalesChannel\Event\SalesChannelContextSwitchEvent;
use Shopware\Core\System\StateMachine\Event\StateMachineStateChangeEvent;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutConfirmSubscriber implements EventSubscriberInterface
{
    private WeRepackSession $session;
    private OrderService $orderService;
    private PromotionService $promotionService;
    private MailService $mailService;
    private ConfigService $configService;
    private WeRepackTelemetryService $weRepackTelemetryService;
    private SalesChannelRepository $salesChannelRepository;

    public function __construct(OrderService $orderService, PromotionService $promotionService, MailService $mailService, ConfigService $configService, WeRepackTelemetryService $weRepackTelemetryService, SalesChannelRepository $salesChannelRepository)
    {
        $this->session = new WeRepackSession();
        $this->orderService = $orderService;
        $this->promotionService = $promotionService;
        $this->mailService = $mailService;
        $this->configService = $configService;
        $this->weRepackTelemetryService = $weRepackTelemetryService;
        $this->salesChannelRepository = $salesChannelRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => 'onCheckoutConfirmPageLoad',
            CheckoutOrderPlacedEvent::class => 'onCheckoutOrderPlaced',
            SalesChannelContextSwitchEvent::class => 'onSalesChannelContextSwitch',
            'state_machine.order_transaction.state_changed' => 'onOrderStateChanged',
        ];
    }

    public function onCheckoutConfirmPageLoad(CheckoutConfirmPageLoadedEvent $event): void
    {
        $event->getPage()->addArrayExtension('MrpixWeRepack', [
            'weRepackEnabled' => $this->session->isWeRepackEnabled(),
        ]);
    }

    public function onCheckoutOrderPlaced(CheckoutOrderPlacedEvent $event): void
    {
        // Write WeRepack data to database
        $this->orderService->writeWeRepackOrder($event->getOrder(), $this->session->isWeRepackEnabled(), $event->getContext());

        // Send telemetry data to WeRepack
        $salesChannel = $this->salesChannelRepository->getSalesChannel($event->getSalesChannelId(), $event->getContext());
        $url = $salesChannel->getDomains()->first()->getUrl();
        $language = explode('-', $salesChannel->getDomains()->first()->getLanguage()->getLocale()->getCode())[0];
        $this->weRepackTelemetryService->sendTelemetryData($url, $language);

        // Clear session
        $this->session->clear();
    }

    public function onOrderStateChanged(StateMachineStateChangeEvent $event): void
    {
        $name = $event->getNextState()->getTechnicalName();
        if ('paid' !== $name) {
            return;
        }

        $order = $this->orderService->getOrderByTransition($event->getTransition(), $event->getContext());
        if (null === $order) {
            return;
        }

        /** @var ?RepackOrderEntity $orderExtension */
        $orderExtension = $order->getExtension('repackOrder');

        // if customer selected WeRepack option and WeRepack is enabled for next order, create promotion code
        $salesChannelId = $order->getSalesChannelId();
        if (!$this->configService->get('createPromotionCodes', $salesChannelId)
            || 'order' != $this->configService->get('couponSendingType', $salesChannelId)
            || !$orderExtension->isRepack()) {
            return;
        }

        // event can be triggered multiple times, but only create promotion code one time
        if (null != $orderExtension->getPromotionIndividualCodeId()) {
            return;
        }

        // create promotion code
        $promotionCode = $this->promotionService->createPromotionIndividualCode($order, $event->getContext(), $salesChannelId);

        // send promotion code to customer
        $this->mailService->send(
            $order,
            $promotionCode,
            $event->getContext(),
            $salesChannelId,
        );
    }

    public function onSalesChannelContextSwitch(SalesChannelContextSwitchEvent $event): void
    {
        // Toggle WeRepack checkbox only if event is triggered by checkbox
        if (1 == $event->getRequestDataBag()->get('mrpixWeRepackToggle')) {
            $this->session->setWeRepackEnabled(!$this->session->isWeRepackEnabled());
        }

        /*
         * Alternatively can be checked if the WeRepack option is enabled:
         * $weRepackEnabled= $event->getRequestDataBag()->get('mrpixWeRepack');
         *
         * because the form field 'mrpixWeRepack' is only sent when the checkbox is enabled
         */
    }
}
