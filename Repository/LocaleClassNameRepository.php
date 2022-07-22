<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\LocaleClassName;
use Symfony\Bridge\Doctrine\RegistryInterface;

class LocaleClassNameRepository extends AbstractRepository
{
    /**
     * LocaleClassNameRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LocaleClassName::class);
    }

}
