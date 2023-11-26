<?php

namespace App\Repository;

use App\Entity\ParsingInstance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParsingInstance>
 *
 * @method ParsingInstance|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParsingInstance|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParsingInstance[]    findAll()
 * @method ParsingInstance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParsingInstanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParsingInstance::class);
    }

//    /**
//     * @return ParsingInstance[] Returns an array of ParsingInstance objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ParsingInstance
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
