<?php

namespace Plugin\MultiLingual\Entity\Master;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Doctrine\Common\Collections\Collection;
use Plugin\MultiLingual\Common\LocaleHelper;

/**
 * @EntityExtension("Eccube\Entity\Master\Job")
 */
trait JobTrait
{
    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Plugin\MultiLingual\Entity\Master\LocaleJob", mappedBy="Parent", cascade={"persist","remove"})
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
        return LocaleJob::class;
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

    // For AbstractMasterTypeExtension
    public function getLocaleName(?string $locale = null): string
    {
        return $this->getLocaleField('name');
    }
}
