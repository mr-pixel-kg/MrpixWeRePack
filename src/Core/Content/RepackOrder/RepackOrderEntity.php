<?php declare(strict_types=1);

namespace Mrpix\WeRepack\Core\Content\RepackOrder;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Promotion\Aggregate\PromotionIndividualCode\PromotionIndividualCodeEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class RepackOrderEntity extends Entity
{
    use EntityIdTrait;

    protected string $orderId;
    protected ?OrderEntity $order = null;

    protected ?string $promotionIndividualCodeId = null;
    protected ?PromotionIndividualCodeEntity $promotionIndividualCode = null;

    protected bool $isRepack;

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function setOrder(?OrderEntity $order): void
    {
        $this->order = $order;
    }

    public function getPromotionIndividualCodeId(): ?string
    {
        return $this->promotionIndividualCodeId;
    }

    public function setPromotionIndividualCodeId(?string $promotionIndividualCodeId): void
    {
        $this->promotionIndividualCodeId = $promotionIndividualCodeId;
    }

    public function getPromotionIndividualCode(): ?PromotionIndividualCodeEntity
    {
        return $this->promotionIndividualCode;
    }

    public function setPromotionIndividualCode(?PromotionIndividualCodeEntity $promotionIndividualCode): void
    {
        $this->promotionIndividualCode = $promotionIndividualCode;
    }

    public function isRepack(): bool
    {
        return $this->isRepack;
    }

    public function setIsRepack(bool $isRepack): void
    {
        $this->isRepack = $isRepack;
    }
}