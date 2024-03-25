<?php

namespace Mrpix\WeRepack\Components;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\AbsolutePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\PercentagePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\AbsolutePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Cart\Rule\LineItemRule;
use Shopware\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountEntity;
use Shopware\Core\Checkout\Promotion\Exception\DiscountCalculatorNotFoundException;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class DiscountCalculator
{
    public function __construct(private readonly PercentagePriceCalculator $percentagePriceCalculator, private readonly AbsolutePriceCalculator $absolutePriceCalculator)
    {
    }

    public function calculateDiscount(PromotionDiscountEntity $discount, LineItem $discountLineItem, LineItemCollection $products, SalesChannelContext $context)
    {
        match ($discount->getType()) {
            PromotionDiscountEntity::TYPE_ABSOLUTE => $this->calculateAbsoluteDiscount($discount, $discountLineItem, $products, $context),
            PromotionDiscountEntity::TYPE_PERCENTAGE => $this->calculatePercentageDiscount($discount, $discountLineItem, $products, $context),
            default => throw new DiscountCalculatorNotFoundException($discount->getType()),
        };
    }

    protected function calculateAbsoluteDiscount(PromotionDiscountEntity $discount, LineItem $discountLineItem, LineItemCollection $products, SalesChannelContext $context)
    {
        // declare price definition to define how this price is calculated
        $definition = new AbsolutePriceDefinition(
            -$discount->getValue(),
            new LineItemRule(Rule::OPERATOR_EQ, $products->getKeys())
        );
        $discountLineItem->setPriceDefinition($definition);

        // calculate price
        $discountLineItem->setPrice(
            $this->absolutePriceCalculator->calculate($definition->getPrice(), $products->getPrices(), $context)
        );
    }

    protected function calculatePercentageDiscount(PromotionDiscountEntity $discount, LineItem $discountLineItem, LineItemCollection $products, SalesChannelContext $context)
    {
        // declare price definition to define how this price is calculated
        $definition = new PercentagePriceDefinition(
            -$discount->getValue(),
            new LineItemRule(Rule::OPERATOR_EQ, $products->getKeys())
        );
        $discountLineItem->setPriceDefinition($definition);

        // calculate price
        $discountLineItem->setPrice(
            $this->percentagePriceCalculator->calculate($definition->getPercentage(), $products->getPrices(), $context)
        );
    }
}