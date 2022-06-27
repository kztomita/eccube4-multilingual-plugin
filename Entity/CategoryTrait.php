<?php

namespace Plugin\MultiLingual\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Plugin\MultiLingual\Common\LocaleHelper;

/**
 * @EntityExtension("Eccube\Entity\Category")
 */
trait CategoryTrait
{
    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Plugin\MultiLingual\Entity\LocaleCategory", mappedBy="Category", cascade={"persist","remove"})
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
        return LocaleCategory::class;
    }

    public function getLocaleName(): string
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('locale', LocaleHelper::getCurrentRequestLocale()));

        $locales = $this->getLocales()->matching($criteria);
        if ($locales->count() == 0) {
            return $this->getName();
        } else {
            return $locales[0]->getName();
        }
    }

    public function getLocaleNameWithLevel(): string
    {
        return str_repeat('　', $this->getHierarchy() - 1).$this->getLocaleName();
    }
}
