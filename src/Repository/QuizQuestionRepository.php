<?php

namespace App\Repository;

use App\Entity\QuizQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuizQuestion>
 */
class QuizQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizQuestion::class);
    }

    /**
     * @return QuizQuestion[] up to $limit questions, randomly picked, never repeated within the draw
     */
    public function findRandomSet(int $limit = 20): array
    {
        $all = $this->createQueryBuilder('q')
            ->getQuery()
            ->getResult();

        shuffle($all);

        return array_slice($all, 0, $limit);
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
