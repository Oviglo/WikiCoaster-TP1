<?php

namespace App\Repository;

use App\Entity\Coaster;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Coaster>
 */
class CoasterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly Security $security)
    {
        parent::__construct($registry, Coaster::class);
    }

    public function findFiltered(
        string $parkId = '',
        string $categoryId = '',
        int $page = 1,
        int $count = 10
    ): Paginator {
        $begin = ($page - 1) * $count; // Calcul de l'offset

        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.park', 'p')
            ->leftJoin('c.categories', 'cat')
            ->setMaxResults($count) // LIMIT
            ->setFirstResult($begin) // OFFSET
        ;

        if ('' !== $parkId) {
            $qb->andWhere('p.id = :parkId')
                ->setParameter('parkId', (int) $parkId)
            ;
        }
        // Filtrer la catégorie
        if ('' !== $categoryId) {
            $qb->andWhere('cat.id = :categoryId')
                ->setParameter('categoryId', (int) $categoryId)
            ;
        }

        if (!$this->security->isGranted('ROLE_ADMIN')) {
            $qb->andWhere('c.published = true OR c.author = :author')
                ->setParameter('author', $this->security->getUser())
            ;
        }

        return new Paginator($qb->getQuery());
    }
}
