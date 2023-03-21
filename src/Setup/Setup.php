<?php declare(strict_types=1);

namespace Mrpix\WeRepack\Setup;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

abstract class Setup
{
    protected const TABLE_NAMES = ['mp_repack_order'];
    protected const TEMPLATE_TYPE_TECHNICAL_NAME = 'mrpix.we_repack.coupon';
    protected const TEMPLATE_TYPE_NAME = [
        'de-DE' => 'Gutscheinmail für Repack Bestellungen',
        'en-GB' => 'Coupon mail for repack orders',
        Defaults::LANGUAGE_SYSTEM => 'Coupon mail for repack orders'
    ];

    protected Context $context;
    protected EntityRepository $mailTemplateTypeRepository;
    protected EntityRepository $mailTemplateRepository;
    protected Connection $connection;

    public function __construct(
        Context $context,
        EntityRepository $mailTemplateTypeRepository,
        EntityRepository $mailTemplateRepository,
        Connection $connection
    )
    {
        $this->context = $context;
        $this->mailTemplateTypeRepository = $mailTemplateTypeRepository;
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->connection = $connection;
    }

    public function run(): void
    {
        $this->adjustMails();
        $this->adjustDatabase();
    }

    protected function getExistingMailTemplateTypeId(string $technicalName): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('technicalName', $technicalName));

        return $this->mailTemplateTypeRepository->searchIds($criteria, $this->context)->firstId();
    }

    abstract protected function adjustMails(): void;
    abstract protected function adjustDatabase(): void;
}