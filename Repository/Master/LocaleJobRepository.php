<?php

namespace Plugin\MultiLingual\Repository\Master;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\Master\LocaleJob;
use Doctrine\Common\Persistence\ManagerRegistry;

class LocaleJobRepository extends AbstractRepository
{
    /**
     * LocaleJobRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocaleJob::class);
    }

}
