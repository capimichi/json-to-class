<?php

namespace App\Repository;

use App\Entity\Element;
use App\Entity\ParsingInstance;
use App\KeyGenerator\ElementKeyGenerator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Element>
 *
 * @method Element|null find($id, $lockMode = null, $lockVersion = null)
 * @method Element|null findOneBy(array $criteria, array $orderBy = null)
 * @method Element[]    findAll()
 * @method Element[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ElementRepository extends ServiceEntityRepository
{
    
    protected $elementsByParsingInstanceAndPath = [];
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Element::class);
    }
    
    public function getElementByParsingInstancePath(
        ParsingInstance $parsingInstance,
        $path
    )
    {
        return $this->findOneBy([
            'parsingInstance' => $parsingInstance,
            'path'            => $path,
        ]);
    }

//    /**
//     * @return Element[] Returns an array of Element objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Element
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
