<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\LocaleCategory;
use Doctrine\Common\Persistence\ManagerRegistry;

class LocaleCategoryRepository extends AbstractRepository
{
    /**
     * LocaleCategoryRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocaleCategory::class);
    }

}
