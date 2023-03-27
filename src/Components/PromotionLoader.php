<?php declare(strict_types=1);

namespace Mrpix\WeRepack\Components;

use Mrpix\WeRepack\Service\ConfigService;
use Shopware\Core\Checkout\Promotion\PromotionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class PromotionLoader
{
    protected EntityRepository $promotionRepository;
    protected ConfigService $configService;

    public function __construct(EntityRepository $promotionRepository, ConfigService $configService)
    {
        $this->promotionRepository = $promotionRepository;
        $this->configService = $configService;
    }

    public function getPromotion(Context $context): ?PromotionEntity
    {
        $promotionId = $this->configService->get('repackPromotion');
        if (empty($promotionId)) {
            return null;
        }

        $criteria = new Criteria([$promotionId]);
        $criteria->addAssociation('discounts');

        return $this->promotionRepository->search($criteria, $context)->getEntities()->first();
    }
}