<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\LocaleDeliveryTime;
use Doctrine\Common\Persistence\ManagerRegistry;

class LocaleDeliveryTimeRepository extends AbstractRepository
{
    /**
     * LocaleDeliveryTimeRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocaleDeliveryTime::class);
    }

}
