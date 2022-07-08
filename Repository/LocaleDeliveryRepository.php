<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\LocaleDelivery;
use Doctrine\Common\Persistence\ManagerRegistry;

class LocaleDeliveryRepository extends AbstractRepository
{
    /**
     * LocaleDeliveryRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocaleDelivery::class);
    }

}
