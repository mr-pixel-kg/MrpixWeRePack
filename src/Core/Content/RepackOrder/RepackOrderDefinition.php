<?php declare(strict_types=1);

namespace Mrpix\WeRepack\Core\Content\RepackOrder;

use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Promotion\Aggregate\PromotionIndividualCode\PromotionIndividualCodeDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class RepackOrderDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'mp_repack_order';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('order_id', 'orderId', OrderDefinition::class))
                ->addFlags(new Required()),
            new FkField(
                'promotion_individual_code_id',
                'promotionIndividualCodeId',
                PromotionIndividualCodeDefinition::class
            ),
            new BoolField('is_repack', 'isRepack'),

            new OneToOneAssociationField(
                'order',
                'order_id',
                'id',
                OrderDefinition::class,
                false
            ),
            new OneToOneAssociationField(
                'promotionIndividualCode',
                'promotion_individual_code_id',
                'id',
                PromotionIndividualCodeDefinition::class,
                false
            )
        ]);
    }
}