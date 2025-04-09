<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('t');
        $today = new \DateTimeImmutable('today');

        if (!empty($filters['title'])) {
            $qb->andWhere('t.name LIKE :title')
               ->setParameter('title', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['status'])) {
            switch ($filters['status']) {
    
                case 'Concluída':
                    $qb->andWhere('t.completed = true');
                    break;

                case 'Agendada':
                    $qb->andWhere('t.completed = false AND t.cancelled = false AND t.deadline >= :today')
                        ->setParameter('today', $today);
                    break;

                case 'Cancelada':
                    $qb->andWhere('t.cancelled = true');
                    break;

                case 'Atrasada':
                    $qb->andWhere('t.completed = false AND t.cancelled = false AND t.deadline < :today')
                       ->setParameter('today', $today);
                    break;
            }
        }

        if (!empty($filters['duedate'])) {
            switch ($filters['duedate']) {
                case 'Passado':
                    $qb->andWhere('t.deadline < :today')
                       ->setParameter('today', $today);
                    break;

                case 'Hoje':
                    $qb->andWhere('t.deadline = :today')
                       ->setParameter('today', $today);
                    break;

                case 'Futuro':
                    $qb->andWhere('t.deadline > :today')
                       ->setParameter('today', $today);
                    break;
            }
        }

        return $qb->orderBy('t.deadline', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    public function save(Task $task): void
    {
        $this->getEntityManager()->persist($task);
        $this->getEntityManager()->flush();
    }

    public function delete(Task $task): void
    {
        $this->getEntityManager()->remove($task);
        $this->getEntityManager()->flush();
    }

    public function findAllTasks(): array
    {
        return $this->findAll();
    }
}