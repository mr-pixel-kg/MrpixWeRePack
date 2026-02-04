<?php

declare(strict_types=1);

namespace Mrpix\WeRepack\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1737113667 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1737113667;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
ALTER TABLE `mp_repack_order` ADD PRIMARY KEY (`id`);
ALTER TABLE `mp_repack_order` DROP CONSTRAINT `fk.mp_repack_order.order_id`;
ALTER TABLE `mp_repack_order` DROP CONSTRAINT `fk.mp_repack_order.promotion_individual_code_id`;
ALTER TABLE `mp_repack_order` ADD CONSTRAINT `fk.mp_repack_order.promotion_individual_code_id` FOREIGN KEY(`promotion_individual_code_id`)
        REFERENCES `promotion_individual_code` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
SQL;

        $connection->executeStatement($query);
    }
}
