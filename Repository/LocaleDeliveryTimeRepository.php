<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\LocaleDeliveryTime;
use Symfony\Bridge\Doctrine\RegistryInterface;

class LocaleDeliveryTimeRepository extends AbstractRepository
{
    /**
     * LocaleDeliveryTimeRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LocaleDeliveryTime::class);
    }

}
