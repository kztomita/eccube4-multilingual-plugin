<?php

namespace Plugin\MultiLingual\Entity\Master;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Doctrine\Common\Collections\Collection;
use Plugin\MultiLingual\Common\LocaleHelper;
use Plugin\MultiLingual\Entity\Master\LocaleProductListOrderBy;

/**
 * @EntityExtension("Eccube\Entity\Master\ProductListOrderBy")
 */
trait ProductListOrderByTrait
{
    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Plugin\MultiLingual\Entity\Master\LocaleProductListOrderBy", mappedBy="Parent", cascade={"persist","remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     *
     */
    private $Locales;

    /**
     * @return Collection
     */
    public function getLocales(): Collection
    {
        return $this->Locales;
    }

    /**
     * Localeクラスのクラス名を返す。
     * getLocales()を実装する場合は、本メソッドも実装すること。
     *
     * @return string
     */
    public static function getLocaleClass(): string
    {
        return LocaleProductListOrderBy::class;
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
}
