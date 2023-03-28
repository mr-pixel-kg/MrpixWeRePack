<?php declare(strict_types=1);

namespace Mrpix\WeRepack\Extension\Content\Order;

use Mrpix\WeRepack\Core\Content\RepackOrder\RepackOrderDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OrderExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return OrderDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToOneAssociationField(
                'repackOrder',
                'id',
                'order_id',
                RepackOrderDefinition::class,
                true
            )
        );
    }
}