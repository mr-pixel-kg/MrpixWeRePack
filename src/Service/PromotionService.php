<?php

namespace Mrpix\WeRepack\Service;

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
    protected $promotionCodeService;
    protected $promotionIndividualCodeRepository;
    protected EntityRepository $weRepackOrderRepository;

    public function __construct(EntityRepository $promotionRepository, ConfigService $configService, PromotionCodeService $promotionCodeService, EntityRepository $promotionIndividualCodeRepository, EntityRepository $weRepackOrderRepository)
    {
        $this->promotionRepository = $promotionRepository;
        $this->configService = $configService;
        $this->promotionCodeService = $promotionCodeService;
        $this->promotionIndividualCodeRepository = $promotionIndividualCodeRepository;
        $this->weRepackOrderRepository = $weRepackOrderRepository;
    }

    public function createPromotionIndividualCode(string $promotionId) : PromotionEntity
    {
        $promotionCode = $this->promotionCodeService->getFixedCode();

        $individualCodeId = Uuid::randomHex();
        $result = $this->promotionIndividualCodeRepository->upsert([
            [
                'id' => $individualCodeId,
                'promotionId' => $promotionId,
                'code' => $promotionCode,
            ],
        ], $this->context);
        dump(['log' => 'create individual promotion code', 'result' => $result, 'code' => $promotionCode]);
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