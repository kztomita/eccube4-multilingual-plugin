<?php

namespace Plugin\MultiLingual\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Plugin\MultiLingual\Common\LocaleHelper;

/**
 * @EntityExtension("Eccube\Entity\DeliveryTime")
 */
trait DeliveryTimeTrait
{
    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Plugin\MultiLingual\Entity\LocaleDeliveryTime", mappedBy="ParentDeliveryTime", cascade={"persist","remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     *
     */
    private $Locales;

    /**
     * @return Collection
     */
    public function getLocales(): Collection
    {
        return $this->Locales ?? new ArrayCollection();
    }

    /**
     * Localeクラスのクラス名を返す。
     * getLocales()を実装する場合は、本メソッドも実装すること。
     *
     * @return string
     */
    public static function getLocaleClass(): string
    {
        return LocaleDeliveryTime::class;
    }

    /**
     * 指定Localeでのフィールド値を返す。
     * getLocales()を実装する場合は、本メソッドも実装すること。
     *
     * @param string $field
     * @param string|null $locale
     * @return mixed
     */
    public function getLocaleField(string $field, ?string $locale = null)
    {
        return LocaleHelper::getLocaleField($this, $field, $locale);
    }
}
