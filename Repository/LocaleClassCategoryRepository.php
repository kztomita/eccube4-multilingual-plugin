<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\LocaleClassCategory;
use Doctrine\Common\Persistence\ManagerRegistry;

class LocaleClassCategoryRepository extends AbstractRepository
{
    /**
     * LocaleClassCategoryRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocaleClassCategory::class);
    }

}
