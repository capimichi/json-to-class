<?php

namespace App\Repository;

use App\Entity\JoinGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JoinGroup>
 *
 * @method JoinGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method JoinGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method JoinGroup[]    findAll()
 * @method JoinGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JoinGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JoinGroup::class);
    }

//    /**
//     * @return JoinGroup[] Returns an array of JoinGroup objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('j')
//            ->andWhere('j.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('j.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?JoinGroup
//    {
//        return $this->createQueryBuilder('j')
//            ->andWhere('j.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
