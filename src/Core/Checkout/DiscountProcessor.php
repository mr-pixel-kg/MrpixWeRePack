<?php

namespace Mrpix\WeRepack\Core\Checkout;

use LogicException;
use Mrpix\WeRepack\Components\DiscountCalculator;
use Mrpix\WeRepack\Components\WeRepackSession;
use Mrpix\WeRepack\Service\ConfigService;
use Mrpix\WeRepack\Service\PromotionService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\AbsolutePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\PercentagePriceCalculator;
use Shopware\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountEntity;
use Shopware\Core\Checkout\Promotion\PromotionEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class DiscountProcessor implements CartProcessorInterface
{
    private WeRepackSession $session;
    private ConfigService $configService;
    private PromotionService $promotionService;
    private DiscountCalculator $discountCalculator;

    public function __construct(PercentagePriceCalculator $percentagePriceCalculator, AbsolutePriceCalculator $absolutePriceCalculator, ConfigService $configService, PromotionService $promotionService)
    {
        $this->discountCalculator = new DiscountCalculator($percentagePriceCalculator, $absolutePriceCalculator);
        $this->session = new WeRepackSession();
        $this->configService = $configService;
        $this->promotionService = $promotionService;
    }

    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $salesChannelId = $context->getSalesChannelId();

        // if customer selected WeRepack option and WeRepack is enabled for cart, add discount
        if (!$this->configService->get('createPromotionCodes', $salesChannelId)
            || $this->configService->get('couponSendingType', $salesChannelId) != 'cart'
            || !$this->session->isWeRepackEnabled()) {
            return;
        }

        $weRepackPromotion = $this->promotionService->getPromotion($context->getContext(), $salesChannelId);
        $products = $this->findProducts($toCalculate);

        // no products found? skip
        // no discount is assigned to the promotion? skip
        if ($products->count() == 0 || $weRepackPromotion->getDiscounts()->count() == 0) {
            return;
        }

        // Throw error when more than one discount is assigned to WeRepack promotion
        if ($weRepackPromotion->getDiscounts()->count() > 1) {
            throw new LogicException('Only one discount is allowed for the WeRepack promotion!');
        }

        $discountLineItem = $this->createDiscount('WEREPACK_DISCOUNT', $weRepackPromotion);
        $discount = $weRepackPromotion->getDiscounts()->first();

        if ($discount->getScope() != PromotionDiscountEntity::SCOPE_CART) {
            throw new LogicException('The discount in the WeRepack promotion can only be applied to cart!');
        }

        // add discount to new cart
        $this->discountCalculator->calculateDiscount($discount, $discountLineItem, $products, $context);
        $toCalculate->add($discountLineItem);
    }

    private function findProducts(Cart $cart): LineItemCollection
    {
        return $cart->getLineItems()->filter(function (LineItem $item) {
            // Only consider products, not custom line items or promotional line items
            if ($item->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
                return false;
            }

            return $item;
        });
    }

    private function createDiscount(string $name, PromotionEntity $promotion): LineItem
    {
        $discountLineItem = new LineItem($name, 'werepack_discount', null, 1);

        $discountLineItem->setLabel($promotion->getName());
        $discountLineItem->setGood(false);
        $discountLineItem->setStackable(false);
        $discountLineItem->setRemovable(false);

        return $discountLineItem;
    }
}