<?php

namespace Mrpix\WeRepack\Repository;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class SalesChannelRepository
{
    public function __construct(protected EntityRepository $salesChannelRepository)
    {
    }

    public function getSalesChannel(string $salesChannelId, Context $context): ?SalesChannelEntity
    {
        $criteria = new Criteria([$salesChannelId]);
        $criteria->addAssociation('domains');
        $criteria->addAssociation('domains.language');
        $criteria->addAssociation('domains.language.locale');

        return $this->salesChannelRepository->search($criteria, $context)->first();
    }
}