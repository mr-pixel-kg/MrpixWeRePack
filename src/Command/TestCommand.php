<?php

namespace Mrpix\WeRepack\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestCommand extends Command
{
    protected static $defaultName = 'mrpixwerepack:test';

    private EntityRepository $werepackOrderRepository;
    private EntityRepository $orderRepository;

    public function __construct(EntityRepository $werepackOrderRepository, EntityRepository $orderRepository)
    {
        parent::__construct(null);
        $this->werepackOrderRepository = $werepackOrderRepository;
        $this->orderRepository = $orderRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title("Test");

        //$this->orderRepository->search(new Criteria(), Context::createDefaultContext())->last()->getId()

        dump($this->werepackOrderRepository->upsert([[
            'orderId' => 'c44975df6f71437783b99d0bd32c347d',
            'promotionIndividualCodeId' => null,
            'isRepack' => true,
        ]], Context::createDefaultContext()));

        return Command::SUCCESS;
    }
}