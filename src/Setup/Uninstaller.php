<?php declare(strict_types=1);

namespace Mrpix\WeRepack\Setup;

class Uninstaller extends Setup
{
    protected function adjustMails(): void
    {
        // TODO: Implement adjustMails() method.
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function adjustDatabase(): void
    {
        foreach (self::TABLE_NAMES as $table) {
            $this->connection->executeStatement('DROP TABLE ' . $table);
        }
    }
}