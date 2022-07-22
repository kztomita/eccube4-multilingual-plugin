<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\LocaleTag;
use Symfony\Bridge\Doctrine\RegistryInterface;

class LocaleTagRepository extends AbstractRepository
{
    /**
     * LocaleTagRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, LocaleTag::class);
    }

}
