<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Task;
use App\Enum\TaskStatus;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $task = new Task();
        $task->setName("Complete the Tasks CRUD");
        $task->setDescription("This is the first task for my project.");
        $task->setDeadline(new \DateTime('2025-03-26'));
        $task->setStatus(TaskStatus::SCHEDULED);

        $manager->persist($task);

        $manager->flush();
    }
}
