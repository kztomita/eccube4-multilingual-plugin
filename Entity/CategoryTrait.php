<?php

namespace Plugin\MultiLingual\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Plugin\MultiLingual\Entity\LocaleCategory;

/**
 * @EntityExtension("Eccube\Entity\Category")
 */
trait CategoryTrait
{
    /**
     * @ORM\OneToMany(targetEntity="Plugin\MultiLingual\Entity\LocaleCategory", mappedBy="Category")
     * @ORM\OrderBy({"id" = "ASC"})
     *
     */
    private $Locales;

    /**
     * @return LocaleCategory[]
     */
    public function getLocales()
    {
        return $this->Locales;
    }
}
