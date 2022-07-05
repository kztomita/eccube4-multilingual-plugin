<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\LocaleClassName;
use Doctrine\Common\Persistence\ManagerRegistry;

class LocaleClassNameRepository extends AbstractRepository
{
    /**
     * LocaleClassNameRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocaleClassName::class);
    }

}
