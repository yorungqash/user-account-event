<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\ListRepositoryInterface;
use App\Constant\UserEventConstant;
use App\Event\UserEvent;
use App\ValueObject\UserEventError;
use RedisException;

final readonly class UserEventService implements UserEventServiceInterface, UserEventConstant
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator,
        private ListRepositoryInterface $listRepository,
        private JsonServiceInterface $jsonService,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function build(array $data): array
    {
        try {
            $data = $this->denormalizer->denormalize($data, UserEvent::class . '[]');
        } catch (ExceptionInterface) {
            return [];
        }

        if (count($this->validator->startContext()->validate($data)->getViolations()) > 0) {
            return [];
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function add(array $userEvents): int|UserEventError
    {
        $userEventClosure = function (array $userEvents) {
            foreach ($userEvents as $userEvent) {
                yield $userEvent;
            }
        };

        $count = 0;

        try {
            /** @var UserEvent $userEvent */
            foreach ($userEventClosure($userEvents) as $userEvent) {
                $this->listRepository->queue(self::QUEUE_NAME . '_' . $userEvent->userId, $userEvent);

                $count += 1;
            }
        } catch (RedisException $e) {
            return new UserEventError($e->getMessage());
        }

        return $count;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(string $queueName): UserEvent|UserEventError
    {
        try {
            $jsonUserEvent = $this->listRepository->dequeue($queueName);
        } catch (RedisException $e) {
            return new UserEventError($e->getMessage());
        }

        /** @var UserEvent $userEvent */
        $userEvent = $this->jsonService->buildObject($jsonUserEvent, UserEvent::class);

        return $userEvent;
    }

    /**
     * {@inheritDoc}
     */
    public function getRandomList(): string|UserEventError
    {
        try {
            $userEventList = $this->listRepository->getQueueByPrefix(self::QUEUE_NAME . '_');
        } catch (RedisException $e) {
            return new UserEventError($e->getMessage());
        }

        return $userEventList;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteList(string $queueName): bool|UserEventError
    {
        try {
            $isDeleted = $this->listRepository->delete($queueName);
        } catch (RedisException $e) {
            return new UserEventError($e->getMessage());
        }

        return $isDeleted;
    }
}