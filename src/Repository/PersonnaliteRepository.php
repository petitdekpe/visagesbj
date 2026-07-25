<?php

namespace App\Repository;

use App\Entity\Personnalite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Personnalite>
 */
class PersonnaliteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personnalite::class);
    }

    /**
     * @return Personnalite[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Personnalite[]
     */
    public function findFeatured(int $limit = 6): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.lastName', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlug(string $slug): ?Personnalite
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * @return Personnalite[]
     */
    public function findRandomOthers(Personnalite $exclude, int $limit = 3): array
    {
        $others = $this->createQueryBuilder('p')
            ->andWhere('p.id != :id')
            ->setParameter('id', $exclude->getId())
            ->getQuery()
            ->getResult();

        shuffle($others);

        return array_slice($others, 0, $limit);
    }

    public function countWithoutPhoto(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.photo IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
