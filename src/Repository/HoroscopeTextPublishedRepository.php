<?php

namespace App\Repository;

use App\Entity\HoroscopeTextPublished;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HoroscopeTextPublished>
 *
 * @method HoroscopeTextPublished|null find($id, $lockMode = null, $lockVersion = null)
 * @method HoroscopeTextPublished|null findOneBy(array $criteria, array $orderBy = null)
 * @method HoroscopeTextPublished[]    findAll()
 * @method HoroscopeTextPublished[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HoroscopeTextPublishedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HoroscopeTextPublished::class);
    }

    public function add(HoroscopeTextPublished $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HoroscopeTextPublished $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return HoroscopeTextPublished[] Returns an array of HoroscopeTextPublished objects
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

//    public function findOneBySomeField($value): ?HoroscopeTextPublished
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
