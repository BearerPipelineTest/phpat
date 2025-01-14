<?php

declare(strict_types=1);

namespace PHPatOld\App\Event\Listener;

use PHPatOld\App\ErrorStorage;
use PHPatOld\EventDispatcher\EventInterface;
use PHPatOld\EventDispatcher\EventListenerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SuiteStartListener implements EventListenerInterface
{
    private OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function __invoke(EventInterface $event)
    {
        ErrorStorage::setStartTime(microtime(true));

        $this->output->writeln('', OutputInterface::VERBOSITY_NORMAL);
        $this->output->writeLn('---/-------\------|-----\---/--', OutputInterface::VERBOSITY_VERBOSE);
        $this->output->writeLn('--/-PHP Architecture Tester/---', OutputInterface::VERBOSITY_VERBOSE);
        $this->output->writeLn('-/-----------\----|-------X----', OutputInterface::VERBOSITY_VERBOSE);
        $this->output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
    }
}
