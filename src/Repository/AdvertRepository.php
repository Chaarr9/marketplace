<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Advert;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Advert>
 */
class AdvertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advert::class);
    }

//    /**
//     * @return Advert[] Returns an array of Advert objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Advert
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findAllPaginated(int $page = 1, int $limit = 16): Paginator
    {
        $query = $this->createQueryBuilder('a')
            ->orderBy('a.id', 'DESC')
            ->getQuery();

        $query
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query);
    }

    public function findAllPaginatedBy(array $attributes = [], int $page = 1, int $limit = 16): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('a');

        foreach ($attributes as $attribute => $value) {
            if (!empty($value)) {
                if (str_contains($attribute, '.')) {
                    // Handle relationships
                    [$relation, $column] = explode('.', $attribute, 2);

                    // Avoid duplicate joins
                    if (!array_key_exists($relation, $queryBuilder->getAllAliases())) {
                        $queryBuilder->join("a.{$relation}", $relation);
                    }

                    $queryBuilder
                        ->andWhere($queryBuilder->expr()->like("{$relation}.{$column}", ":{$relation}_{$column}"))
                        ->setParameter("{$relation}_{$column}", "%{$value}%");
                } else {
                    // Handle attributes directly on the main entity
                    $queryBuilder
                        ->andWhere($queryBuilder->expr()->like("a.{$attribute}", ":{$attribute}"))
                        ->setParameter($attribute, "%{$value}%");
                }
            }
        }

        $queryBuilder
            ->orderBy('a.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        // Generate Query
        $query = $queryBuilder->getQuery();

        // Use Doctrine Paginator
        return new Paginator($query);
    }

    public function findAllPaginatedForUser(User $user, int $page = 1, int $limit = 16): Paginator
    {
        $query = $this->createQueryBuilder('a')
            ->andWhere('a.user = :val')
            ->setParameter('val', $user)
            ->orderBy('a.id', 'DESC')
            ->getQuery();

        $query
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($query);
    }
}
