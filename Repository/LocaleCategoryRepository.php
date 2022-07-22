<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\LocaleCategory;
use Symfony\Bridge\Doctrine\RegistryInterface;

class LocaleCategoryRepository extends AbstractRepository
{
    /**
     * LocaleCategoryRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LocaleCategory::class);
    }

}
