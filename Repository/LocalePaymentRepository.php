<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\LocalePayment;
use Doctrine\Common\Persistence\ManagerRegistry;

class LocalePaymentRepository extends AbstractRepository
{
    /**
     * LocalePaymentRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocalePayment::class);
    }

}
