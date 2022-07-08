<?php

namespace Plugin\MultiLingual\Repository\Master;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\Master\LocaleCustomerOrderStatus;
use Doctrine\Common\Persistence\ManagerRegistry;

class LocaleCustomerOrderStatusRepository extends AbstractRepository
{
    /**
     * LocaleCustomerOrderStatusRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocaleCustomerOrderStatus::class);
    }

}
