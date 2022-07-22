<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\LocaleProduct;
use Symfony\Bridge\Doctrine\RegistryInterface;

class LocaleProductRepository extends AbstractRepository
{
    /**
     * LocaleProductRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LocaleProduct::class);
    }

}
