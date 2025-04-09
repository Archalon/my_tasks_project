<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testTaskProperties(): void
    {
        $task = new Task();
        $deadline = new \DateTimeImmutable('2025-04-07');

        $task->setName('Nova Tarefa');
        $task->setDescription('Descrição de teste');
        $task->setDeadline($deadline);
        $task->setCompleted(true);

        $this->assertSame('Nova Tarefa', $task->getName());
        $this->assertSame('Descrição de teste', $task->getDescription());
        $this->assertSame($deadline, $task->getDeadline());
        $this->assertTrue($task->isCompleted());
        $this->assertFalse($task->isCancelled());
    }

    public function testCancelAndReactivate(): void
    {
        $task = new Task();

        $task->cancel();
        $this->assertTrue($task->isCancelled(), 'A tarefa deveria estar cancelada.');

        $task->reactivate();
        $this->assertFalse($task->isCancelled(), 'A tarefa deveria ter sido reativada.');
    }
}