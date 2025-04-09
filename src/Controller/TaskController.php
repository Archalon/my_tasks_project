<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskEditType;
use App\Form\TaskType;
use App\Form\TaskFilterType;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TaskController extends AbstractController
{
    public function __construct(
        private TaskService $taskService
    ) {}

    #[Route('/tasks', name: 'task_index')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(TaskFilterType::class);
        $form->handleRequest($request);

        $filters = $form->getData() ?? [];
        $tasks = $this->taskService->getFilteredTasks($filters);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'filterForm' => $form->createView(),
        ]);
    }

    #[Route('task/{id<\d+>}', name: 'show_task')]
    public function show(Task $task): Response
    {    
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/task/new', name: 'new_task')]
    public function new(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($this->taskService->processCreation($task, $form)) {
            $this->addFlash('notice', 'Task created successfully!');

            return $this->redirectToRoute('show_task', [
                'id' => $task->getId(),
            ]);
        }

        return $this->render('task/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/task/{id<\d+>}/edit', name: 'edit_task')]
    public function edit(Task $task, Request $request): Response
    {
        if ($task->isCancelled()) {
            $this->addFlash('notice', 'Tarefa cancelada não pode ser editada.');
            return $this->redirectToRoute('show_task', ['id' => $task->getId()]);
        }
    
        $originalDeadline = clone $task->getDeadline();
        $form = $this->createForm(TaskEditType::class, $task);
        $form->handleRequest($request);

        if ($this->taskService->processEdit($task, $form, $originalDeadline)) {
            $this->addFlash('notice', 'Task updated successfully!');
            return $this->redirectToRoute('show_task', [
                'id' => $task->getId(),
            ]);
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form,
            'task' => $task,
        ]);
    }

    #[Route('/task/{id<\d+>}/delete', name: 'delete_task')]
    public function delete(Task $task): Response
    {
        $this->taskService->delete($task);

        $this->addFlash('notice', 'Task deleted successfully!');

        return $this->redirectToRoute('task_index');
    }

    #[Route('/task/{id<\d+>}/cancel', name: 'cancel_task')]
    public function cancel(Task $task): Response
    {
        $this->taskService->cancel($task);
        $this->addFlash('notice', 'Task canceled successfully.');

        return $this->redirectToRoute('task_index');
    }

    #[Route('/task/{id<\d+>}/reactivate', name: 'reactivate_task')]
    public function reactivate(Task $task): Response
    {
        $this->taskService->reactivate($task);
        $this->addFlash('notice', 'Tarefa rescheduled successfully.');

        return $this->redirectToRoute('task_index');
    }

}
