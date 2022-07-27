<?php

namespace Plugin\MultiLingual\Repository\Master;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\Master\LocaleSex;
use Doctrine\Common\Persistence\ManagerRegistry;

class LocaleSexRepository extends AbstractRepository
{
    /**
     * LocaleSexRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocaleSex::class);
    }

}
