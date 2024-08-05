<?php

namespace Mrpix\WeRepack\Service;

use Mrpix\WeRepack\Repository\SalesChannelRepository;
use Mrpix\WeRepack\Setup\Setup;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Mail\Service\AbstractMailService;
use Shopware\Core\Content\MailTemplate\Aggregate\MailTemplateType\MailTemplateTypeEntity;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Validation\DataBag\DataBag;

class MailService
{
    protected AbstractMailService $mailService;
    protected EntityRepository $mailTemplateTypeRepository;
    protected SalesChannelRepository $salesChannelRepository;

    public function __construct(AbstractMailService $mailService, EntityRepository $mailTemplateTypeRepository, SalesChannelRepository $salesChannelRepository)
    {
        $this->mailService = $mailService;
        $this->mailTemplateTypeRepository = $mailTemplateTypeRepository;
        $this->salesChannelRepository = $salesChannelRepository;
    }

    public function send(OrderEntity $order, string $promotionCode, Context $context, string $salesChannelId)
    {
        $mailTemplate = $this->getMailTemplate($context);
        if (null === $mailTemplate) {
            return;
        }
        $customer = $order->getOrderCustomer();
        $data = $this->buildMailDataBag($customer, $mailTemplate, $salesChannelId, $context);

        $this->mailService->send(
            $data->all(),
            $context,
            [
                'order' => $order,
                'salesChannel' => $this->salesChannelRepository->getSalesChannel($salesChannelId, $context),
                'promotionCode' => $promotionCode,
                'promotion' => $promotionCode
            ]
        );
    }

    protected function getMailTemplate(Context $context): ?MailTemplateEntity
    {
        $criteria = new Criteria();
        $criteria
            ->addAssociation('mailTemplates')
            ->addAssociation('mailTemplates.translations')
            ->addFilter(new EqualsFilter('technicalName', Setup::MAIL_TEMPLATE_TYPE_TECHNICAL_NAME));

        /** @var null|MailTemplateTypeEntity $mailTemplateType */
        $mailTemplateType = $this->mailTemplateTypeRepository->search($criteria, $context)->first();
        if (null === $mailTemplateType) {
            return null;
        }

        return $mailTemplateType->getMailTemplates()->first();
    }

    private function buildMailDataBag(OrderCustomerEntity $customer, MailTemplateEntity $mailTemplate, string $salesChannelId, Context $context): DataBag
    {
        $translations = $mailTemplate->getTranslations();
        $data = new DataBag();
        $data->set(
            'recipients',
            [
                $customer->getEmail() => $customer->getFirstName() . ' ' . $customer->getLastName()
            ]
        );
        $data->set('senderName', $mailTemplate->getSenderName());
        $data->set('salesChannelId', $salesChannelId);

        if (null === $translations) {
            $data->set('senderName', $mailTemplate->getSenderName());
            $data->set('subject', $mailTemplate->getSubject());
            $data->set('contentPlain', $mailTemplate->getContentPlain());
            $data->set('contentHtml', $mailTemplate->getContentHtml());
        } else {
            foreach ($translations->getElements() as $translation) {
                if ($translation->getLanguageId() !== $context->getLanguageId()) {
                    continue;
                }
                $data->set('senderName', $translation->getSenderName());
                $data->set('subject', $translation->getSubject());
                $data->set('contentPlain', $translation->getContentPlain());
                $data->set('contentHtml', $translation->getContentHtml());
            }
        }

        return $data;
    }
}
