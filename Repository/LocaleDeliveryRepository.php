<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\LocaleDelivery;
use Symfony\Bridge\Doctrine\RegistryInterface;

class LocaleDeliveryRepository extends AbstractRepository
{
    /**
     * LocaleDeliveryRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LocaleDelivery::class);
    }

}
