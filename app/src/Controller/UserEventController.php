<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Service\JsonServiceInterface;
use App\Service\UserEventServiceInterface;
use App\ValueObject\UserEventError;

final class UserEventController extends AbstractController
{
    public function __construct(
        private readonly JsonServiceInterface $jsonService,
        private readonly UserEventServiceInterface $userEventService,
    ) {
    }

    #[Route('/api/v1/user/event', methods: ['OPTIONS', 'POST'])]
    #[Route('/api/v1/user/event/', methods: ['OPTIONS', 'POST'])]
    public function add(HttpRequest $request): HttpResponse
    {
        if (!$this->jsonService->isJson($request->getContent())) {
            return new HttpResponse(
                $this->jsonService->buildJson(['error' => 'only json is accepted']),
                HttpResponse::HTTP_BAD_REQUEST
            );
        }

        $userEvents = $this->userEventService->build($request->toArray());

        if ($userEvents === []) {
            return new HttpResponse(
                $this->jsonService->buildJson(['error' => 'invalid json structure']),
                HttpResponse::HTTP_BAD_REQUEST
            );
        }

        $countUserEvents = $this->userEventService->add($userEvents);

        if ($countUserEvents instanceof UserEventError) {
            return new HttpResponse($countUserEvents->message, HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new HttpResponse(
            $this->jsonService->buildJson(['added' => $countUserEvents]),
            HttpResponse::HTTP_OK
        );
    }
}
