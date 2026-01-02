<?php

namespace App\Repository;

use App\Entity\Advice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Advice>
 */
class AdviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advice::class);
    }

    /**
     * Find all advices that apply to a specific month
     *
     * @return Advice[]
     */
    public function findByMonth(int $month): array
    {
        $advices = $this->findAll();

        return array_filter($advices, fn(Advice $advice) => $advice->appliesToMonth($month));
    }
}
