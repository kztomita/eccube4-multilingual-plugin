<?php

namespace Plugin\MultiLingual\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Eccube\Entity\OrderItem;
use Plugin\MultiLingual\Entity\LocaleProduct;

/**
 * @EntityExtension("Eccube\Entity\OrderItem")
 */
trait OrderItemTrait
{
    /**
     * @var ?string
     *
     * @ORM\Column(name="locale_product_name", type="string", length=255, nullable=true)
     */
    private $locale_product_name;

    /**
     * @var ?string
     *
     * @ORM\Column(name="locale_class_name1", type="string", length=255, nullable=true)
     */
    private $locale_class_name1;

    /**
     * @var ?string
     *
     * @ORM\Column(name="locale_class_name2", type="string", length=255, nullable=true)
     */
    private $locale_class_name2;

    /**
     * @var ?string
     *
     * @ORM\Column(name="locale_class_category_name1", type="string", length=255, nullable=true)
     */
    private $locale_class_category_name1;

    /**
     * @var ?string
     *
     * @ORM\Column(name="locale_class_category_name2", type="string", length=255, nullable=true)
     */
    private $locale_class_category_name2;

    /**
     * Set LocaleProductName.
     *
     * @param ?string $productName
     *
     * @return self
     */
    public function setLocaleProductName(?string $productName): self
    {
        $this->locale_product_name = $productName;

        return $this;
    }

    /**
     * Get LocaleProductName.
     *
     * @return ?string
     */
    public function getLocaleProductName(): ?string
    {
        return $this->locale_product_name;
    }

    /**
     * Set LocaleClassName1.
     *
     * @param ?string $name
     *
     * @return self
     */
    public function setLocaleClassName1(?string $name): self
    {
        $this->locale_class_name1 = $name;

        return $this;
    }

    /**
     * Get LocaleClassName1.
     *
     * @return ?string
     */
    public function getLocaleClassName1(): ?string
    {
        return $this->locale_class_name1;
    }

    /**
     * Set LocaleClassName2.
     *
     * @param ?string $name
     *
     * @return self
     */
    public function setLocaleClassName2(?string $name): self
    {
        $this->locale_class_name2 = $name;

        return $this;
    }

    /**
     * Get LocaleClassName2.
     *
     * @return ?string
     */
    public function getLocaleClassName2(): ?string
    {
        return $this->locale_class_name2;
    }

    /**
     * Set LocaleClassCategoryName1.
     *
     * @param ?string $name
     *
     * @return self
     */
    public function setLocaleClassCategoryName1(?string $name): self
    {
        $this->locale_class_category_name1 = $name;

        return $this;
    }

    /**
     * Get LocaleClassCategoryName1.
     *
     * @return ?string
     */
    public function getLocaleClassCategoryName1(): ?string
    {
        return $this->locale_class_category_name1;
    }

    /**
     * Set LocaleClassCategoryName2.
     *
     * @param ?string $name
     *
     * @return self
     */
    public function setLocaleClassCategoryName2(?string $name): self
    {
        $this->locale_class_category_name2 = $name;

        return $this;
    }

    /**
     * Get LocaleClassCategoryName2.
     *
     * @return ?string
     */
    public function getLocaleClassCategoryName2(): ?string
    {
        return $this->locale_class_category_name2;
    }
}
