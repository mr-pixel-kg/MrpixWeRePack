<?php

namespace Mrpix\WeRepack\Subscriber;

use Mrpix\WeRepack\Components\WeRepackSession;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\System\SalesChannel\Event\SalesChannelContextSwitchEvent;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutConfirmSubscriber implements EventSubscriberInterface
{
    private $session;

    public function __construct()
    {
        $this->session = new WeRepackSession();
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
        dump($this->session->isWeRepackEnabled());

    }

    public function onCheckoutOrderPlaced(CheckoutOrderPlacedEvent $event)
    {
        dump($event);
    }

    public function onSalesChannelContextSwitch(SalesChannelContextSwitchEvent $event)
    {
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannel()->getId();

        // Toggle WeRepack checkbox only if event is triggered by checkbox
        if($event->getRequestDataBag()->get('mrpixWeRepackToggle') == 1) {
            $this->session->setWeRepackEnabled(!$this->session->isWeRepackEnabled());

            /*
             * Alternatively can be checked if the WeRepack option is enabled:
             * $weRepackEnabled= $event->getRequestDataBag()->get('mrpixWeRepack');
             *
             * because the form field 'mrpixWeRepack' is only sent when the checkbox is enabled
             */
        }

    }
}