<?php

declare(strict_types=1);

namespace Mrpix\WeRepack\Extension\Content\PromotionIndividualCode;

use Mrpix\WeRepack\Core\Content\RepackOrder\RepackOrderDefinition;
use Shopware\Core\Checkout\Promotion\Aggregate\PromotionIndividualCode\PromotionIndividualCodeDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PromotionIndividualCodeExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return PromotionIndividualCodeDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToOneAssociationField(
                'repackOrder',
                'id',
                'promotion_individual_code_id',
                RepackOrderDefinition::class,
                false,
            ),
        );
    }
}
