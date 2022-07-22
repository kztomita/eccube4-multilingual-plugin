<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\LocaleClassCategory;
use Symfony\Bridge\Doctrine\RegistryInterface;

class LocaleClassCategoryRepository extends AbstractRepository
{
    /**
     * LocaleClassCategoryRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LocaleClassCategory::class);
    }

}
