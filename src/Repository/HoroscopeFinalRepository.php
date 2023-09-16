<?php

namespace App\Repository;

use App\Entity\HoroscopeFinal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HoroscopeFinal>
 *
 * @method HoroscopeFinal|null find($id, $lockMode = null, $lockVersion = null)
 * @method HoroscopeFinal|null findOneBy(array $criteria, array $orderBy = null)
 * @method HoroscopeFinal[]    findAll()
 * @method HoroscopeFinal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HoroscopeFinalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HoroscopeFinal::class);
    }

    public function add(HoroscopeFinal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HoroscopeFinal $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
