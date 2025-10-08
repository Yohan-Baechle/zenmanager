<?php

namespace App\Controller\Api;

use App\Entity\Clock;
use App\Entity\User;
use App\Repository\ClockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Clocks')]
class ClockController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route('/clocks', name: 'api_clocks_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/clocks',
        summary: 'Set the arrival/departure of the authenticated user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['time', 'status', 'userId'],
                properties: [
                    new OA\Property(
                        property: 'time',
                        description: 'Clock time',
                        type: 'string',
                        format: 'date-time',
                        example: '2025-10-08T09:00:00+00:00'
                    ),
                    new OA\Property(
                        property: 'status',
                        description: 'true for clock-in (arrival), false for clock-out (departure)',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'userId',
                        description: 'User ID',
                        type: 'integer',
                        example: 1
                    )
                ]
            )
        ),
        tags: ['Clocks'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Clock entry created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'time', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'owner',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                                new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                                new OA\Property(property: 'lastName', type: 'string', example: 'Doe')
                            ],
                            type: 'object'
                        ),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'errors', type: 'object')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'User not found'
            )
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['userId'])) {
                return $this->json(['error' => 'userId is required'], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->em->getRepository(User::class)->find($data['userId']);
            if (!$user) {
                return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
            }

            $clock = $this->serializer->deserialize(
                $request->getContent(),
                Clock::class,
                'json'
            );

            $clock->setOwner($user);

            $errors = $this->validator->validate($clock);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
            }

            $this->em->persist($clock);
            $this->em->flush();

            return $this->json($clock, Response::HTTP_CREATED, [], ['groups' => ['clock:read', 'user:read']]);
        } catch (ExceptionInterface $e) {
            return $this->json(['error' => 'Invalid data format'], Response::HTTP_BAD_REQUEST);
        }
    }
}
