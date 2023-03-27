<?php declare(strict_types=1);

namespace Mrpix\WeRepack\Setup;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class Uninstaller extends Setup
{
    protected function adjustMails(): void
    {
        $mailTemplateTypeId = $this->getExistingMailTemplateTypeId(self::MAIL_TEMPLATE_TYPE_TECHNICAL_NAME);
        $mailTemplateIds = $this->getMailTemplateIds($mailTemplateTypeId);
        $ids = [];
        foreach ($mailTemplateIds as $mailTemplateId) {
            $ids[] = ['id' => $mailTemplateId];
        }

        if (!empty($ids)) {
            $this->mailTemplateRepository->delete($ids, $this->context);
        }

        $this->mailTemplateTypeRepository->delete([['id' => $mailTemplateTypeId]], $this->context);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    protected function adjustDatabase(): void
    {
        foreach (self::DATABASE_TABLE_NAMES as $table) {
            $this->connection->executeStatement('DROP TABLE ' . $table);
        }
    }

    private function getMailTemplateIds(string $mailTemplateTypeId): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('mailTemplateTypeId', $mailTemplateTypeId));

        $result = $this->mailTemplateRepository->searchIds($criteria, $this->context);
        return $result->getIds();
    }
}