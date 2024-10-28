<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\UserEventServiceInterface;
use App\Event\UserEvent;
use App\ValueObject\UserEventError;
use Throwable;

#[AsCommand(
    name: 'app:user-event',
    description: 'Starting UserEvent queue',
    hidden: false
)]
final class UserEventCommand extends Command
{
    private bool $forceFinish;

    public function __construct(
        private readonly UserEventServiceInterface $userEventService,
    ) {
        parent::__construct();
    }

    /**
     * @throws Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->forceFinish = false;
        pcntl_signal(SIGTERM, [$this, 'signalHandler']);
        pcntl_signal(SIGINT, [$this, 'signalHandler']);

        do {
            $userEventList = $this->userEventService->getRandomList();

            if ($userEventList instanceof UserEventError) {
                //no queues
                usleep(50000);
                continue;
            }

            $userEvent = $this->processEvent($userEventList);

            if ($userEvent instanceof UserEventError) {
                $this->userEventService->deleteList($userEventList);
            }

            echo var_export($userEvent, true) . PHP_EOL;
            usleep(50000);
        } while (!$this->forceFinish);

        return Command::FAILURE;
    }

    public function signalHandler(int $signalNumber): void
    {
        echo 'Signal catch: ' . $signalNumber . PHP_EOL;

        match ($signalNumber) {
            SIGTERM, SIGINT => $this->forceFinish = true,
            default => null
        };
    }

    private function processEvent(string $queueName): UserEvent|UserEventError
    {
        $userEvent = $this->userEventService->remove($queueName);

        sleep(1);

        $this->userEventService->deleteList($queueName . 'blocked');

        return $userEvent;
    }
}
