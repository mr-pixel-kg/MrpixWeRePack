<?php declare(strict_types=1);

namespace Mrpix\WeRepack\Core\Content\RepackOrder;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class RepackOrderCollection extends EntityCollection
{
    public function getExpectedClass(): string
    {
        return RepackOrderEntity::class;
    }
}