<?php declare(strict_types=1);

namespace Mrpix\WeRepack\Subscriber;

use Mrpix\WeRepack\Components\PromotionLoader;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\GenericPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StorefrontSubscriber implements EventSubscriberInterface
{
    private SystemConfigService $configService;
    private PromotionLoader $promotionLoader;

    public function __construct(SystemConfigService $configService, PromotionLoader $promotionLoader)
    {
        $this->configService = $configService;
        $this->promotionLoader = $promotionLoader;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            GenericPageLoadedEvent::class => 'onPageLoaded'
        ];
    }

    public function onPageLoaded(GenericPageLoadedEvent $event): void
    {
        if (!$this->configService->get('MrpixWeRepack.config.createPromotionCodes') ||
            $this->configService->get('MrpixWeRepack.config.couponSendingType') !== 'cart' ||
            !$this->configService->get('MrpixWeRepack.config.repackPromotion')) {
            return;
        }

        $promotion = $this->promotionLoader->getPromotion($event->getContext());
        if ($promotion === null) {
            return;
        }

        $page = $event->getPage();
        $page->setExtensions(['repackPromotion' => $promotion]);
    }
}