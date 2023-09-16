<?php

namespace App\Repository;

use App\Entity\AstrologicalSign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AstrologicalSign>
 *
 * @method AstrologicalSign|null find($id, $lockMode = null, $lockVersion = null)
 * @method AstrologicalSign|null findOneBy(array $criteria, array $orderBy = null)
 * @method AstrologicalSign[]    findAll()
 * @method AstrologicalSign[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AstrologicalSignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AstrologicalSign::class);
    }

    public function add(AstrologicalSign $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AstrologicalSign $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return AstrologicalSign[] Returns an array of AstrologicalSign objects
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

//    public function findOneBySomeField($value): ?AstrologicalSign
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
