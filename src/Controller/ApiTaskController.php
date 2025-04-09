<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Dto\TaskInputDto;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api/tasks', name: 'api_task_')]
class ApiTaskController extends AbstractController
{
    public function __construct(
        private TaskRepository $taskRepository,
        private TaskService $taskService,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $tasks = $this->taskRepository->findAll();
        return $this->json($tasks, 200, [], ['groups' => 'task:read']);
    }

    #[Route('/filter', name: 'filter', methods: ['GET'])]
    public function filter(Request $request): JsonResponse
    {
        $filters = array_filter([
            'title'   => $request->query->get('title'),
            'status'  => $request->query->get('status'),
            'duedate' => $request->query->get('due_date') ?? $request->query->get('duedate'),
        ], fn($value) => $value !== null && $value !== '');

        $tasks = $this->taskService->getFilteredTasks($filters);

        return $this->json($tasks, 200, [], ['groups' => 'task:read']);
    }

    #[Route('/{id<\d+>}', name: 'show', methods: ['GET'])]
    public function show(Task $task): JsonResponse
    {
        return $this->json($task, 200, [], ['groups' => 'task:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] TaskInputDto $inputDto,
        ValidatorInterface $validator
    ): JsonResponse {
        $errors = $validator->validate($inputDto);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new JsonResponse($errorsString, 400);
        }

        $task = new Task();
        $task->setName($inputDto->name);
        $task->setDescription($inputDto->description);
        $task->setDeadline($inputDto->deadline);
        $task->setCompleted(false);

        $this->taskService->save($task);

        return $this->json($task, Response::HTTP_CREATED, [], ['groups' => 'task:read']);
    }

    #[Route('/{id<\d+>}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, Task $task): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $task->setName($data['name'] ?? $task->getName());
        $task->setDescription($data['description'] ?? $task->getDescription());

        if (isset($data['deadline'])) {
            $task->setDeadline(new \DateTimeImmutable($data['deadline']));
        }

        $task->setCompleted($data['completed'] ?? $task->isCompleted());

        $this->taskService->save($task);

        return $this->json($task, 200, [], ['groups' => 'task:read']);
    }

    #[Route('/{id<\d+>}', name: 'delete', methods: ['DELETE'])]
    public function delete(Task $task): JsonResponse
    {
        $this->entityManager->remove($task);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id<\d+>}/cancel', name: 'cancel', methods: ['POST'])]
    public function cancel(Task $task): JsonResponse
    {
        $task->setCancelled(true);
        $this->taskService->save($task);

        return $this->json(['message' => 'Task cancelled.'], 200);
    }

    #[Route('/{id<\d+>}/uncancel', name: 'uncancel', methods: ['POST'])]
    public function uncancel(Task $task): JsonResponse
    {
        $task->setCancelled(false);
        $this->taskService->save($task);

        return $this->json(['message' => 'Task reactivated.'], 200);
    }
}
