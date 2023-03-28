<?php

namespace Mrpix\WeRepack\Service;

use Mrpix\WeRepack\Setup\Setup;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Promotion\PromotionEntity;
use Shopware\Core\Content\Mail\Service\AbstractMailService;
use Shopware\Core\Content\MailTemplate\Aggregate\MailTemplateType\MailTemplateTypeEntity;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class MailService
{
    protected AbstractMailService $mailService;
    protected EntityRepository $mailTemplateTypeRepository;
    protected EntityRepository $salesChannelRepository;

    public function __construct(AbstractMailService $mailService, EntityRepository $mailTemplateTypeRepository, EntityRepository $salesChannelRepository) {
        $this->mailService = $mailService;
        $this->mailTemplateTypeRepository = $mailTemplateTypeRepository;
        $this->salesChannelRepository = $salesChannelRepository;
    }

    public function send(OrderEntity $order, string $promotionCode, PromotionEntity $promotion, Context $context, string $salesChannelId)
    {
        $mailTemplate = $this->getMailTemplate($context);
        if ($mailTemplate === null) {
            return;
        }
        $customer = $order->getOrderCustomer();
        $translations = $mailTemplate->getTranslations();

        $data = new DataBag();
        $data->set(
            'recipients',
            [
                $customer->getEmail() => $customer->getFirstName().' '.$customer->getLastName()
            ]
        );

        $data->set('senderName', $mailTemplate->getSenderName());

        if ($translations === null) {
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
        $data->set('salesChannelId', $salesChannelId);

        $this->mailService->send(
            $data->all(),
            $context, [
                'order' => $order,
                'salesChannel' => $this->getSalesChannel($salesChannelId, $context),
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

        /** @var MailTemplateTypeEntity|null $mailTemplateType */
        $mailTemplateType = $this->mailTemplateTypeRepository->search($criteria, $context)->first();
        if ($mailTemplateType === null) {
            return null;
        }

        return $mailTemplateType->getMailTemplates()->first();
    }

    private function getSalesChannel(string $salesChannelId, Context $context): ?SalesChannelEntity
    {
        $criteria = new Criteria([$salesChannelId]);
        $criteria->addAssociation('domains');

        return $this->salesChannelRepository->search($criteria, $context)->first();
    }
}