<?php

namespace App\ValueObject;

use Closure;
use Fiber;
use FiberError;
use Throwable;
use Symfony\Component\Process\Process;

final class FiberList
{
    /**
     * @var array<Fiber<mixed, mixed, mixed, mixed>>
     */
    private array $fibers;

    /** @var array<mixed> */
    private array $parameters;

    private int $processCount;

    /**
     * @param array<mixed> $closures
     * @param array<mixed> $closureParameters
     */
    public function __construct(
        private array $closures = [],
        private readonly array $closureParameters = [],
    ) {
        $this->clear();
        $this->checkClosures();
    }

    public function clear(): void
    {
        $this->fibers = [];
        $this->parameters = [];
        $this->processCount = 0;
    }

    public function isEmpty(): bool
    {
        return $this->fibers === [];
    }

    private function checkClosures(): void
    {
        if (count($this->closures) !== count($this->closureParameters)) {
            $this->clear();

            return;
        }

        $checked = true;

        array_walk($this->closures, function (mixed $closure, int $index) use (&$checked) {
            if (!$closure instanceof Closure) {
                $checked = false;

                return;
            }

            /** @var array<mixed> $parameters */
            $parameters = $this->closureParameters[$index];

            $this->add($closure, $parameters);
        });

        if (!$checked) {
            $this->clear();
        }
    }

    /**
     * @param Closure $fiberClosure
     * @param array<mixed> $fiberClosureParameters
     */
    public function add(Closure $fiberClosure, array $fiberClosureParameters = []): void
    {
        $this->fibers[] = new Fiber($fiberClosure);
        $this->parameters[] = $fiberClosureParameters;
        $this->processCount += 1;
    }

    public function setProcessCount(int $processCount): void
    {
        if ($processCount <= 0) {
            return;
        }

        $this->processCount = $processCount;
    }

    /**
     * @throws FiberError|Throwable
     * @return array<int<0, max>, mixed>
     */
    public function run(): array
    {
        if ($this->fibers === []) {
            return [];
        }

        $fiberListResult = [];

        foreach ($this->fibers as $index => $unstartedFiber) {
            if (!is_iterable($this->parameters[$index])) {
                continue;
            }

            $unstartedFiber->start(...$this->parameters[$index]);

            if (count($this->fibers) > $this->processCount) {
                foreach ($this->waitForFibers(1) as $fiber) {
                    $fiberListResult[] = $fiber->getReturn();
                }
            }
        }

        foreach ($this->waitForFibers() as $fiber) {
            $fiberListResult[] = $fiber->getReturn();
        }

        return $fiberListResult;
    }

    /**
     * @param int|null $completionCount
     * @return array<Fiber<mixed, mixed, mixed, mixed>>
     * @throws FiberError|Throwable
     */
    private function waitForFibers(?int $completionCount = null): array
    {
        $completedFibers = [];
        $completionCount ??= count($this->fibers);

        $countFibers = count($this->fibers);
        $countCompleted = 0;

        while ($countFibers && $countCompleted < $completionCount) {
            foreach ($this->fibers as $index => $fiber) {
                if ($fiber->isSuspended()) {
                    $fiber->resume();
                }

                if ($fiber->isTerminated()) {
                    $completedFibers[] = $fiber;

                    unset($this->fibers[$index]);
                }
            }

            $countFibers = count($this->fibers);
            $countCompleted = count($completedFibers);
        }

        return $completedFibers;
    }

    /**
     * @param int $sleepCheck
     * @return Closure
     */
    public static function getProcessClosure(int $sleepCheck = 0): Closure
    {
        return static function (string $command) use ($sleepCheck): bool {
            $process = Process::fromShellCommandline($command);

            $process->start();

            do {
                Fiber::suspend();

                usleep($sleepCheck);

                $status = $process->isRunning();
            } while ($status);

            return $process->isSuccessful();
        };
    }
}
