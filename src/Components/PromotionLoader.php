<?php declare(strict_types=1);

namespace Mrpix\WeRepack\Components;

use Shopware\Core\Checkout\Promotion\PromotionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class PromotionLoader
{
    protected EntityRepositoryInterface $promotionRepository;
    protected SystemConfigService $configService;

    public function __construct(EntityRepositoryInterface $promotionRepository, SystemConfigService $configService)
    {
        $this->promotionRepository = $promotionRepository;
        $this->configService = $configService;
    }

    public function getPromotion(Context $context): ?PromotionEntity
    {
        $promotionId = $this->configService->get('MrpixWeRepack.config.repackPromotion');
        if (empty($promotionId)) {
            return null;
        }

        $criteria = new Criteria([$promotionId]);

        return $this->promotionRepository->search($criteria, $context)->getEntities()->first();
    }
}