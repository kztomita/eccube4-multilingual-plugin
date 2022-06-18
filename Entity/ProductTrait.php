<?php

namespace Plugin\MultiLingual\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Plugin\MultiLingual\Entity\LocaleProduct;

/**
 * @EntityExtension("Eccube\Entity\Product")
 */
trait ProductTrait
{
    /**
     * @ORM\OneToMany(targetEntity="Plugin\MultiLingual\Entity\LocaleProduct", mappedBy="Product", cascade={"persist","remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     *
     */
    private $Locales;

    /**
     * @return LocaleProduct[]
     */
    public function getLocales(): array
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
        return LocaleProduct::class;
    }
}
