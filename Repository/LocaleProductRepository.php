<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\LocaleProduct;
use Doctrine\Common\Persistence\ManagerRegistry;

class LocaleProductRepository extends AbstractRepository
{
    /**
     * LocaleProductRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocaleProduct::class);
    }

}
