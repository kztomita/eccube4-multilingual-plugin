<?php

namespace Plugin\MultiLingual\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\Category")
 */
trait CategoryTrait
{
    /**
     * @var ?Collection
     *
     * @ORM\OneToMany(targetEntity="Plugin\MultiLingual\Entity\LocaleCategory", mappedBy="Category", cascade={"persist","remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     *
     */
    private $Locales;

    /**
     * @return ?Collection
     */
    public function getLocales(): ?Collection
    {
        return $this->Locales;
    }

    /**
     * Localeクラスのクラス名を返す。
     * getLocales()を実装する場合は、本メソッドも実装すること。
     *
     * @return string
     */
    public function getLocaleClass(): string
    {
        return LocaleCategory::class;
    }
}
