<?php

namespace Mrpix\WeRepack\Subscriber;

use Mrpix\WeRepack\Components\WeRepackSession;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SalesChannel\Event\SalesChannelContextSwitchEvent;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutConfirmSubscriber implements EventSubscriberInterface
{
    private WeRepackSession $session;
    private EntityRepository $werepackOrderRepository;

    public function __construct(EntityRepository $werepackOrderRepository)
    {
        $this->session = new WeRepackSession();
        $this->werepackOrderRepository = $werepackOrderRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => 'onCheckoutConfirmPageLoad',
            CheckoutOrderPlacedEvent::class => 'onCheckoutOrderPlaced',
            SalesChannelContextSwitchEvent::class => 'onSalesChannelContextSwitch'
        ];
    }

    public function onCheckoutConfirmPageLoad(CheckoutConfirmPageLoadedEvent $event)
    {
        $event->getPage()->addArrayExtension('MrpixWeRepack', [
            'weRepackEnabled' => $this->session->isWeRepackEnabled()
        ]);
    }

    public function onCheckoutOrderPlaced(CheckoutOrderPlacedEvent $event)
    {
        $this->werepackOrderRepository->upsert([[
            'orderId' => $event->getOrder()->getId(),
            'promotionIndividualCodeId' => null,
            'isRepack' => $this->session->isWeRepackEnabled(),
        ]], $event->getContext());
    }

    public function onSalesChannelContextSwitch(SalesChannelContextSwitchEvent $event)
    {
        // Toggle WeRepack checkbox only if event is triggered by checkbox
        if($event->getRequestDataBag()->get('mrpixWeRepackToggle') == 1) {
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