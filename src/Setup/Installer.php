<?php declare(strict_types=1);

namespace Mrpix\WeRepack\Setup;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;

class Installer extends Setup
{
    protected function adjustMails(): void
    {
        $existingMailTemplateTypeId = $this->getExistingMailTemplateTypeId(self::MAIL_TEMPLATE_TYPE_TECHNICAL_NAME);
        $mailTemplateTypeId = $existingMailTemplateTypeId ?? $this->createMailTemplateTypeId();

        $this->createMailTemplate($mailTemplateTypeId);
    }

    protected function createMailTemplateTypeId(): string
    {
        $mailTemplateTypeId = Uuid::randomHex();
        $mailTemplateType = [
            'id' => $mailTemplateTypeId,
            'name' => self::MAIL_TEMPLATE_TYPE_NAME,
            'technicalName' => self::MAIL_TEMPLATE_TYPE_TECHNICAL_NAME,
            'availableEntities' => [
                'promotionIndividualCode' => 'promotionIndividualCode',
                'salesChannel' => 'salesChannel'
            ]
        ];

        $this->mailTemplateTypeRepository->create([$mailTemplateType], $this->context);

        return $mailTemplateTypeId;
    }

    private function createMailTemplate(string $mailTemplateTypeId): void
    {
        $mailTemplateTypeDir = __DIR__ . '/../Resources/views/mails/';

        $mailTemplate = [
            'id' => Uuid::randomHex(),
            'mailTemplateTypeId' => $mailTemplateTypeId,
            'subject' => [
                'de-DE' => 'Ihr persönlicher Gutschein für Ihre Bestellung',
                'en-GB' => 'Your personal coupon for your order',
                Defaults::LANGUAGE_SYSTEM => 'Your personal coupon for your order'
            ],
            'senderName' => [
                'de-DE' => '{{ salesChannel.name }}',
                'en-GB' => '{{ salesChannel.name }}',
                Defaults::LANGUAGE_SYSTEM => '{{ salesChannel.name }}'
            ],
            'contentPlain' => [
                'de-DE' => file_get_contents($mailTemplateTypeDir . 'content_plain_de.html.twig'),
                'en-GB' => file_get_contents($mailTemplateTypeDir . 'content_plain_en.html.twig'),
                Defaults::LANGUAGE_SYSTEM => file_get_contents($mailTemplateTypeDir . 'content_plain_en.html.twig')
            ],
            'contentHtml' => [
                'de-DE' => file_get_contents($mailTemplateTypeDir . 'content_html_de.html.twig'),
                'en-GB' => file_get_contents($mailTemplateTypeDir . 'content_html_en.html.twig'),
                Defaults::LANGUAGE_SYSTEM => file_get_contents($mailTemplateTypeDir . 'content_html_en.html.twig')
            ]
        ];

        $this->mailTemplateRepository->create([$mailTemplate], $this->context);
    }

    protected function adjustDatabase(): void
    {
        // Do nothing
    }

}