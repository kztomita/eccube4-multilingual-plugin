<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\MultiLingual\Entity\LocaleTag;
use Doctrine\Common\Persistence\ManagerRegistry;

class LocaleTagRepository extends AbstractRepository
{
    /**
     * LocaleTagRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocaleTag::class);
    }

}
