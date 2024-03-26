<?php declare(strict_types=1);

namespace Mrpix\WeRepack\Subscriber;

use Mrpix\WeRepack\Service\PromotionService;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\GenericPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StorefrontSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly SystemConfigService $configService, private readonly PromotionService $promotionService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            GenericPageLoadedEvent::class => 'onPageLoaded'
        ];
    }

    public function onPageLoaded(GenericPageLoadedEvent $event): void
    {
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannelId();
        if (!$this->configService->get('MrpixWeRepack.config.createPromotionCodes', $salesChannelId) ||
            $this->configService->get('MrpixWeRepack.config.couponSendingType', $salesChannelId) !== 'cart' ||
            !$this->configService->get('MrpixWeRepack.config.repackPromotion', $salesChannelId)) {
            return;
        }

        $promotion = $this->promotionService->getPromotion($event->getContext(), $salesChannelId);
        if ($promotion === null) {
            return;
        }

        $page = $event->getPage();
        $page->setExtensions(['repackPromotion' => $promotion]);
    }
}