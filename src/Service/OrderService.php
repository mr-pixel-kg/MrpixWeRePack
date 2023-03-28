<?php

namespace Mrpix\WeRepack\Service;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\StateMachine\Transition;

class OrderService
{
    protected EntityRepository $orderRepository;
    protected EntityRepository $orderTransactionRepository;
    protected EntityRepository $weRepackOrderRepository;

    public function __construct(EntityRepository $orderRepository, EntityRepository $orderTransactionRepository, EntityRepository $weRepackOrderRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->weRepackOrderRepository = $weRepackOrderRepository;
    }

    public function writeWeRepackOrder(OrderEntity $order, bool $isWeRepackEnabled, Context $context)
    {
        $this->weRepackOrderRepository->upsert([
            [
                'orderId' => $order->getId(),
                'promotionIndividualCodeId' => null,
                'isRepack' => $isWeRepackEnabled,
            ]
        ], $context);
    }

    public function writeIndividualPromotionCodeToWeRepackOrder(OrderEntity $order, string $promotionIndividualCodeId, Context $context)
    {
        $this->weRepackOrderRepository->update([
            [
                'id' => $order->getExtension('repackOrder')->getId(),
                'promotionIndividualCodeId' => $promotionIndividualCodeId,
            ]
        ], $context);
    }

    public function getOrderByTransition(Transition $transition, Context $context): ?OrderEntity
    {
        /** @var OrderTransactionEntity|null $transaction */
        $transaction = $this->orderTransactionRepository->search(new Criteria([$transition->getEntityId()]), $context)->first();
        if ($transaction === null) {
            return null;
        }
        $criteria = new Criteria([$transaction->getOrderId()]);
        $criteria->addAssociation('repackOrder');

        return $this->orderRepository->search($criteria, $context)->first();
    }
}