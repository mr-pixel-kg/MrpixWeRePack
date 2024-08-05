<?php

namespace Mrpix\WeRepack\Service;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Promotion\PromotionEntity;
use Shopware\Core\Checkout\Promotion\Util\PromotionCodeService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;

class PromotionService
{
    protected EntityRepository $promotionRepository;
    protected ConfigService $configService;
    protected PromotionCodeService $promotionCodeService;
    protected EntityRepository $promotionIndividualCodeRepository;
    protected OrderService $orderService;

    public function __construct(EntityRepository $promotionRepository, ConfigService $configService, PromotionCodeService $promotionCodeService, EntityRepository $promotionIndividualCodeRepository, OrderService $orderService)
    {
        $this->promotionRepository = $promotionRepository;
        $this->configService = $configService;
        $this->promotionCodeService = $promotionCodeService;
        $this->promotionIndividualCodeRepository = $promotionIndividualCodeRepository;
        $this->orderService = $orderService;
    }

    public function createPromotionIndividualCode(OrderEntity $order, Context $context, string $salesChannelId): string
    {
        // Load promotion from config
        $promotion = $this->getPromotion($context, $salesChannelId);

        // Generate individual promotion code
        $promotionCode = $this->promotionCodeService->getFixedCode();

        // Write individual promotion code to database
        $individualCodeId = Uuid::randomHex();
        $this->promotionIndividualCodeRepository->upsert([
            [
                'id' => $individualCodeId,
                'promotionId' => $promotion->getId(),
                'code' => $promotionCode,
            ],
        ], $context);

        // Assign individual promotion code to WeRepack order
        $this->orderService->writeIndividualPromotionCodeToWeRepackOrder($order, $individualCodeId, $context);

        return $promotionCode;
    }

    public function getPromotion(Context $context, string $salesChannelId): ?PromotionEntity
    {
        $promotionId = $this->configService->get('repackPromotion', $salesChannelId);
        if (empty($promotionId)) {
            return null;
        }

        $criteria = new Criteria([$promotionId]);
        $criteria->addAssociation('discounts');

        return $this->promotionRepository->search($criteria, $context)->getEntities()->first();
    }
}
