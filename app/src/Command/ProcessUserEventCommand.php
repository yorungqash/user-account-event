<?php

namespace App\Command;

use App\ValueObject\UserEventError;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\UserEventServiceInterface;

#[AsCommand(
    name: 'app:process-user-event',
    description: 'Processing UserEvent queue',
    hidden: false
)]
final class ProcessUserEventCommand extends Command
{
    public function __construct(
        private readonly UserEventServiceInterface $userEventService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userEvent = $this->userEventService->remove();

        if ($userEvent instanceof UserEventError) {
            return Command::FAILURE;
        }

        echo var_export($userEvent, true);

        sleep(1);

        return Command::SUCCESS;
    }
}
