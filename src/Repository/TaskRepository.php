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

    public function getFullComments(Task $task): array
    {
        $conn  = $this->getEntityManager()->getConnection();

        $query = "SELECT
            t.id AS task_id,
            c.id AS comment_id,
            c.content AS content,
            u.id AS author_id,
            CONCAT(u.firstname, ' ', u.lastname) AS author,
            audit.created_at AS created_at
        FROM task t
        LEFT JOIN comment c ON c.task_id = t.id
        LEFT JOIN comment_audit audit ON audit.object_id = c.id
        LEFT JOIN user u ON u.id = audit.blame_id
        WHERE t.id = :taskId AND audit.type = 'insert'
        ORDER BY audit.created_at DESC";

        $stmt = $conn->prepare($query);
        $stmt->bindValue('taskId', $task->getId());

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    public function getTaskProgression(Task $task)
    {
        $conn  = $this->getEntityManager()->getConnection();

        $query = "SELECT
            t.id AS id,
            t.initial_end_date AS initial_end_date,
            t.closing_date AS closing_date,
            audit.created_at AS created_at
        FROM task t
        LEFT JOIN task_audit audit ON audit.object_id = t.id
        WHERE t.id = :taskId AND audit.type = 'insert'
        LIMIT 1";

        $stmt = $conn->prepare($query);
        $stmt->bindValue('taskId', $task->getId());

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    //    /**
    //     * @return Task[] Returns an array of Task objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Task
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
