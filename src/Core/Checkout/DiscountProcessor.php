<?php

namespace Mrpix\WeRepack\Core\Checkout;

use Mrpix\WeRepack\Components\PromotionLoader;
use Mrpix\WeRepack\Components\WeRepackSession;
use Mrpix\WeRepack\Service\ConfigService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\AbsolutePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\PercentagePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\AbsolutePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Cart\Rule\LineItemRule;
use Shopware\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountEntity;
use Shopware\Core\Checkout\Promotion\Exception\DiscountCalculatorNotFoundException;
use Shopware\Core\Checkout\Promotion\PromotionEntity;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class DiscountProcessor implements CartProcessorInterface
{
    private PercentagePriceCalculator $percentagePriceCalculator;
    private AbsolutePriceCalculator $absolutePriceCalculator;
    private WeRepackSession $session;
    private ConfigService $configService;
    private PromotionLoader $promotionLoader;

    public function __construct(PercentagePriceCalculator $percentagePriceCalculator, AbsolutePriceCalculator $absolutePriceCalculator, ConfigService $configService, PromotionLoader $promotionLoader)
    {
        $this->percentagePriceCalculator = $percentagePriceCalculator;
        $this->absolutePriceCalculator = $absolutePriceCalculator;
        $this->session = new WeRepackSession();
        $this->configService = $configService;
        $this->promotionLoader = $promotionLoader;
    }

    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        // if customer selected WeRepack option and WeRepack is enabled for cart, add discount
        if(!$this->configService->get('createPromotionCodes')
            || $this->configService->get('couponSendingType') != 'cart'
            || !$this->session->isWeRepackEnabled()) {
            return;
        }

        $weRepackPromotion = $this->promotionLoader->getPromotion($context->getContext());
        //dump($weRepackPromotion);

        $products = $this->findExampleProducts($toCalculate);

        // no example products found? early return
        if ($products->count() === 0) {
            return;
        }

        // Skip if no discount is assigned to the promotion
        if($weRepackPromotion->getDiscounts()->count() == 0){
            return;
        }

        // Throw error when more than one discount is assigned to WeRepack promotion
        if($weRepackPromotion->getDiscounts()->count() > 1){
            throw new \LogicException('Only one discount is allowed for the WeRepack promotion!');
        }

        $discountLineItem = $this->createDiscount('WEREPACK_DISCOUNT', $weRepackPromotion);
        $discount = $weRepackPromotion->getDiscounts()->first();

        if($discount->getScope() != PromotionDiscountEntity::SCOPE_CART){
            throw new \LogicException('The discount in the WeRepack promotion can only be applied to cart!');
        }

        switch ($discount->getType()) {
            case PromotionDiscountEntity::TYPE_ABSOLUTE:
                // declare price definition to define how this price is calculated
                $definition = new AbsolutePriceDefinition(
                    -$weRepackPromotion->getDiscounts()->first()->getValue(),
                    new LineItemRule(Rule::OPERATOR_EQ, $products->getKeys())
                );

                $discountLineItem->setPriceDefinition($definition);

                // calculate price
                $discountLineItem->setPrice(
                    $this->absolutePriceCalculator->calculate($definition->getPrice(), $products->getPrices(), $context)
                );

                break;

            case PromotionDiscountEntity::TYPE_PERCENTAGE:
                // declare price definition to define how this price is calculated
                $definition = new PercentagePriceDefinition(
                    -$weRepackPromotion->getDiscounts()->first()->getValue(),
                    new LineItemRule(Rule::OPERATOR_EQ, $products->getKeys())
                );

                $discountLineItem->setPriceDefinition($definition);

                // calculate price
                $discountLineItem->setPrice(
                    $this->percentagePriceCalculator->calculate($definition->getPercentage(), $products->getPrices(), $context)
                );

                break;

            default:
                throw new DiscountCalculatorNotFoundException($discount->getType());
        }


        // add discount to new cart
        $toCalculate->add($discountLineItem);
    }

    private function findExampleProducts(Cart $cart): LineItemCollection
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