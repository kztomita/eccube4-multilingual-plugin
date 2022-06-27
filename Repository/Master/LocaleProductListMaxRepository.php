<?php

namespace Plugin\MultiLingual\Repository\Master;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\Master\LocaleProductListMax;
use Doctrine\Common\Persistence\ManagerRegistry;

class LocaleProductListMaxRepository extends AbstractRepository
{
    /**
     * LocaleProductListMaxRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocaleProductListMax::class);
    }

}
