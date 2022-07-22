<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\LocalePayment;
use Symfony\Bridge\Doctrine\RegistryInterface;

class LocalePaymentRepository extends AbstractRepository
{
    /**
     * LocalePaymentRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LocalePayment::class);
    }

}
