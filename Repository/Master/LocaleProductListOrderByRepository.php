<?php

namespace Plugin\MultiLingual\Repository\Master;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\Master\LocaleProductListOrderBy;
use Doctrine\Common\Persistence\ManagerRegistry;

class LocaleProductListOrderByRepository extends AbstractRepository
{
    /**
     * LocaleProductListOrderByRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocaleProductListOrderBy::class);
    }

}
