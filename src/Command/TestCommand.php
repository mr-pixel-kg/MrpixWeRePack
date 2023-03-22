<?php

namespace Mrpix\WeRepack\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestCommand extends Command
{
    protected static $defaultName = 'mrpixwerepack:test';

    private EntityRepository $orderRepository;

    public function __construct(EntityRepository $orderRepository)
    {
        parent::__construct(null);
        $this->orderRepository = $orderRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title("Test");

        /*dump($this->orderRepository->upsert([[
            'id' => '0f04741421364433aeea69023c7429ff',
            'repackOrder' => [
                'promotionIndividualCode' => null,
                'isRepack' => true,
            ]
        ]], Context::createDefaultContext()));*/

        dump($this->orderRepository->search(new Criteria([]), Context::createDefaultContext())->last());


        return Command::SUCCESS;
    }
}