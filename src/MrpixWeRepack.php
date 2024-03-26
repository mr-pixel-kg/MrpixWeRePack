<?php declare(strict_types=1);

namespace Mrpix\WeRepack;

use Doctrine\DBAL\Connection;
use Mrpix\WeRepack\Setup\Installer;
use Mrpix\WeRepack\Setup\Uninstaller;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;

class MrpixWeRepack extends Plugin
{
    //public $connection;
    public function install(InstallContext $installContext): void
    {
        $this->runSetup($installContext->getContext());
    }

    private function runSetup(Context $context, bool $isInstall = true): void
    {
        /** @var EntityRepositoryInterface $mailTemplateTypeRepository */
        $mailTemplateTypeRepository = $this->container->get('mail_template_type.repository');

        /** @var EntityRepositoryInterface $mailTemplateRepository */
        $mailTemplateRepository = $this->container->get('mail_template.repository');

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        if ($isInstall) {
            $runner = new Installer(
                $context,
                $mailTemplateTypeRepository,
                $mailTemplateRepository,
                $connection
            );
        } else {
            $runner = new Uninstaller(
                $context,
                $mailTemplateTypeRepository,
                $mailTemplateRepository,
                $connection
            );
        }

        $runner->run();
    }

    public function update(UpdateContext $updateContext): void
    {
        $this->runSetup($updateContext->getContext());
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->runSetup($uninstallContext->getContext(), false);
    }
}