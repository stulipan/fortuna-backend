<?php

namespace App\Repository;

use App\Entity\HoroscopeText;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HoroscopeText>
 *
 * @method HoroscopeText|null find($id, $lockMode = null, $lockVersion = null)
 * @method HoroscopeText|null findOneBy(array $criteria, array $orderBy = null)
 * @method HoroscopeText[]    findAll()
 * @method HoroscopeText[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HoroscopeTextRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HoroscopeText::class);
    }

    public function add(HoroscopeText $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HoroscopeText $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByQuery(array $criteria = [], array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('h');

        foreach ($criteria as $field => $value) {
            $qb->andWhere("h.$field = :$field")
                ->setParameter($field, $value);
        }

        if ($orderBy !== null) {
            foreach ($orderBy as $field => $direction) {
                $qb->addOrderBy("h.$field", $direction);
            }
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        if ($offset !== null) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery();
    }


//    public function findAllQuery(string $locale)
//    {
//        $qb = $this->createQueryBuilder('h')
//            ->where('h.locale = :locale')
//            ->setParameter('locale', $locale)
//            ->orderBy('h.id', 'ASC');
//
//        return $qb
//            ->getQuery()
//            ;
//    }

    public function findByCursor(string $locale, ?string $cursor, int $pageSize)
    {
        $qb = $this->createQueryBuilder('h')
            ->where('h.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('h.id', 'ASC'); // Order by the appropriate field

        if ($cursor) {
            $qb->andWhere('h.id > :cursor')
                ->setParameter('cursor', $cursor);
        }

        return $qb->setMaxResults($pageSize)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return HoroscopeText[] Returns an array of HoroscopeText objects
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

//    public function findOneBySomeField($value): ?HoroscopeText
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
