<?php

namespace App\Controller;

use App\Entity\Advice;
use App\Repository\AdviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class AdviceController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AdviceRepository $adviceRepository,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('/advice', name: 'api_advice_list_current', methods: ['GET'])]
    public function listCurrentMonth(): JsonResponse
    {
        $currentMonth = (int) date('n');
        return $this->getAdvicesByMonth($currentMonth);
    }

    #[Route('/advice/{month}', name: 'api_advice_list_by_month', methods: ['GET'], requirements: ['month' => '\d+'])]
    public function listByMonth(int $month): JsonResponse
    {
        if ($month < 1 || $month > 12) {
            return $this->json(
                ['error' => 'Invalid month. Month must be between 1 and 12'],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->getAdvicesByMonth($month);
    }

    private function getAdvicesByMonth(int $month): JsonResponse
    {
        $advices = $this->adviceRepository->findByMonth($month);

        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        $data = array_map(fn(Advice $advice) => [
            'id' => $advice->getId(),
            'content' => $advice->getContent(),
            'months' => $advice->getMonths()
        ], $advices);

        return $this->json([
            'month' => $month,
            'monthName' => $monthNames[$month],
            'count' => count($data),
            'advices' => array_values($data)
        ]);
    }

    #[Route('/advice', name: 'api_advice_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['content'])) {
            return $this->json(['error' => 'Content is required'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['months']) || !is_array($data['months'])) {
            return $this->json(['error' => 'Months array is required'], Response::HTTP_BAD_REQUEST);
        }

        $advice = new Advice();
        $advice->setContent($data['content']);
        $advice->setMonths($data['months']);

        $errors = $this->validator->validate($advice);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($advice);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Advice created successfully',
            'advice' => [
                'id' => $advice->getId(),
                'content' => $advice->getContent(),
                'months' => $advice->getMonths()
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/advice/{id}', name: 'api_advice_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $advice = $this->adviceRepository->find($id);

        if (!$advice) {
            return $this->json(['error' => 'Advice not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['content'])) {
            $advice->setContent($data['content']);
        }

        if (isset($data['months'])) {
            if (!is_array($data['months'])) {
                return $this->json(['error' => 'Months must be an array'], Response::HTTP_BAD_REQUEST);
            }
            $advice->setMonths($data['months']);
        }

        $errors = $this->validator->validate($advice);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Advice updated successfully',
            'advice' => [
                'id' => $advice->getId(),
                'content' => $advice->getContent(),
                'months' => $advice->getMonths()
            ]
        ]);
    }

    #[Route('/advice/{id}', name: 'api_advice_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $advice = $this->adviceRepository->find($id);

        if (!$advice) {
            return $this->json(['error' => 'Advice not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($advice);
        $this->entityManager->flush();

        return $this->json(['message' => 'Advice deleted successfully']);
    }
}
