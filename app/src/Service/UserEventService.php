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
        try {
            $countUserEvents = $this->listRepository->enqueueBatch(self::QUEUE_NAME, $userEvents);
        } catch (RedisException $e) {
            return new UserEventError($e->getMessage());
        }

        return $countUserEvents;
    }

    /**
     * {@inheritDoc}
     */
    public function getListLength(): int|UserEventError
    {
        try {
            $listLength = $this->listRepository->length(self::QUEUE_NAME);
        } catch (RedisException $e) {
            return new UserEventError($e->getMessage());
        }

        return $listLength;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(): UserEvent|UserEventError
    {
        try {
            $jsonUserEvent = $this->listRepository->dequeue(self::QUEUE_NAME);
        } catch (RedisException $e) {
            return new UserEventError($e->getMessage());
        }

        /** @var UserEvent $userEvent */
        $userEvent = $this->jsonService->buildObject($jsonUserEvent, UserEvent::class);

        return $userEvent;
    }
}