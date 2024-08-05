<?php

declare(strict_types=1);

namespace Mrpix\WeRepack\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1655304770 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1655304770;
    }

    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $query = <<<SQL
CREATE TABLE IF NOT EXISTS `mp_repack_order` (
    `id` BINARY(16) NOT NULL,
    `order_id` BINARY(16) NOT NULL,
    `promotion_individual_code_id` BINARY(16) NULL,
    `is_repack` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    CONSTRAINT `fk.mp_repack_order.order_id` FOREIGN KEY(`order_id`)
        REFERENCES `order` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.mp_repack_order.promotion_individual_code_id` FOREIGN KEY(`promotion_individual_code_id`)
        REFERENCES `promotion_individual_code` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;;
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // Nothing to do
    }
}
