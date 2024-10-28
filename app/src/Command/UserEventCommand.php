<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\UserEventServiceInterface;
use App\ValueObject\FiberList;
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

    private const int PROCESS_COUNT = 5;

    private const int DEFAULT_SLEEP_CHECK_IN_MICROSECONDS = 75000;

    private const string PROCESS_USER_EVENT_COMMAND = 'php /app/bin/console app:process-user-event';

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
            $listLength = $this->userEventService->getListLength();

            if ($listLength instanceof UserEventError) {
                return Command::FAILURE;
            }

            if ($listLength === 0) {
                usleep(250000);
                continue;
            }

            $processCount = $listLength < self::PROCESS_COUNT ? 1 : self::PROCESS_COUNT;

            $closures = [];
            $closureParameters = [];

            for ($count = 1; $count <= $processCount; $count++) {
                $closures[] = FiberList::getProcessClosure(self::DEFAULT_SLEEP_CHECK_IN_MICROSECONDS);
                $closureParameters[] = [self::PROCESS_USER_EVENT_COMMAND];
            }

            $fibers = new FiberList($closures, $closureParameters);

            $fiberResults = $fibers->run();

            foreach ($fiberResults as $fiberResult) {
                if ($fiberResult !== 0) {
                    break;
                }
            }

            usleep(150000);
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
}
