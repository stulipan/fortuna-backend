<?php

namespace App\Repository;

use App\Entity\HoroscopeRaw;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HoroscopeRaw>
 *
 * @method HoroscopeRaw|null find($id, $lockMode = null, $lockVersion = null)
 * @method HoroscopeRaw|null findOneBy(array $criteria, array $orderBy = null)
 * @method HoroscopeRaw[]    findAll()
 * @method HoroscopeRaw[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HoroscopeRawRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HoroscopeRaw::class);
    }

    public function add(HoroscopeRaw $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HoroscopeRaw $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return HoroscopeRaw[] Returns an array of HoroscopeRaw objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?HoroscopeRaw
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
