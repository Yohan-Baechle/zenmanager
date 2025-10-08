<?php

namespace App\Controller\Api;

use App\Entity\Team;
use App\Repository\TeamRepository;
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

class TeamController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/team', name: 'api_team_index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository): JsonResponse
    {
        $teams = $teamRepository->findAll();
        return $this->json($teams, Response::HTTP_OK, [], ['groups' => 'team:read']);
    }

    #[Route('/team', name: 'api_team_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = $request->toArray();

            $team = $this->serializer->deserialize(
                $request->getContent(),
                Team::class,
                'json'
            );

            // Set timestamps
            if (!$team->getCreatedAt()) {
                $team->setCreatedAt(new \DateTimeImmutable());
            }
            if (!$team->getUpdatedAt()) {
                $team->setUpdatedAt(new \DateTimeImmutable());
            }

            $errors = $this->validator->validate($team);
            if (count($errors) > 0) {
                return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
            }

            $this->em->persist($team);
            $this->em->flush();

            return $this->json($team, Response::HTTP_CREATED, [], ['groups' => 'team:read']);
        } catch (ExceptionInterface $e) {
            return $this->json(['error' => 'Invalid data format'], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/team/{id}', name: 'api_team_update', methods: ['PUT'])]
    public function update(Team $team, Request $request): JsonResponse
    {
        try {
            $this->serializer->deserialize(
                $request->getContent(),
                Team::class,
                'json',
                ['object_to_populate' => $team]
            );

            // Update timestamp
            $team->setUpdatedAt(new \DateTimeImmutable());

            $errors = $this->validator->validate($team);
            if (count($errors) > 0) {
                return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
            }

            $this->em->flush();

            return $this->json($team, Response::HTTP_OK, [], ['groups' => 'team:read']);
        } catch (ExceptionInterface $e) {
            return $this->json(['error' => 'Invalid data format'], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/team/{id}', name: 'api_team_delete', methods: ['DELETE'])]
    public function delete(Team $team): JsonResponse
    {
        $this->em->remove($team);
        $this->em->flush();

        return $this->json(['message' => 'Team deleted successfully']);
    }
}
