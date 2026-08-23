<?php

namespace App\Repository;

use App\Entity\Personnalite;
use App\Enum\PersonnaliteCategory;
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
     * Same as findAllOrdered() but restricted to personnalités marked visible —
     * what the public site (index, homepage, "découvrez aussi") shows. The
     * admin listing uses findAllOrdered() so hidden entries stay manageable.
     *
     * @return Personnalite[]
     */
    public function findVisibleOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.visible = true')
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
            ->andWhere('p.visible = true')
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
            ->andWhere('p.visible = true')
            ->setParameter('id', $exclude->getId())
            ->getQuery()
            ->getResult();

        shuffle($others);

        return array_slice($others, 0, $limit);
    }

    /**
     * Visible personnalités with an actual photo on file — used by the
     * /galerie infinite wall, which has nothing to show for someone without one.
     *
     * @return Personnalite[]
     */
    public function findVisibleWithPhoto(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.visible = true')
            ->andWhere('p.photo IS NOT NULL')
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Categories actually assigned to at least one visible personnalité, in
     * enum declaration order — drives the public filter chips so a category
     * only ever appears once it's genuinely in use, never as an empty option.
     *
     * @return PersonnaliteCategory[]
     */
    public function findVisibleCategoriesInUse(): array
    {
        $rows = $this->createQueryBuilder('p')
            ->select('DISTINCT p.category')
            ->andWhere('p.visible = true')
            ->andWhere('p.category IS NOT NULL')
            ->getQuery()
            ->getResult();

        $inUse = array_column($rows, 'category');

        return array_values(array_filter(
            PersonnaliteCategory::cases(),
            static fn (PersonnaliteCategory $category) => in_array($category, $inUse, true)
        ));
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
