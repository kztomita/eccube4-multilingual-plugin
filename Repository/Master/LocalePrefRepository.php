<?php

namespace Plugin\MultiLingual\Repository\Master;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\Master\LocalePref;
use Doctrine\Common\Persistence\ManagerRegistry;

class LocalePrefRepository extends AbstractRepository
{
    /**
     * LocaleSexRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocalePref::class);
    }

}
