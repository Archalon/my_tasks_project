<?php

namespace App\Service;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

class TaskService
{
    public function __construct(
        private TaskRepository $taskRepository
    ) {}

    public function save(Task $task): void
    {
        $this->taskRepository->save($task);
    }

    public function delete(Task $task): void
    {
        $this->taskRepository->delete($task);
    }

    public function getAllTasks(): array
    {
        return $this->taskRepository->findAllTasks();
    }

    public function processCreation(Task $task, FormInterface $form): bool
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $this->save($task);
            return true;
        }

        return false;
    }

    public function isInvalidDeadlineChange(Task $task, \DateTimeInterface $originalDeadline): bool
    {
        $newDeadline = $task->getDeadline();
        return $originalDeadline != $newDeadline && $newDeadline < new \DateTimeImmutable();
    }

    public function processEdit(Task $task, FormInterface $form, \DateTimeInterface $originalDeadline): bool
    {
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->isInvalidDeadlineChange($task, $originalDeadline)) {
                $form->get('deadline')->addError(
                    new FormError('Não podes escolher uma data no passado.')
                );
                return false;
            }

            $this->save($task);
            return true;
        }

        return false;
    }

    public function cancel(Task $task): void
    {
        $task->cancel();
        $this->save($task);
    }

    public function reactivate(Task $task): void
    {
        $task->reactivate();
        $this->save($task);
    }

    public function getFilteredTasks(array $filters): array
    {
        return $this->taskRepository->findByFilters($filters);
    }
}