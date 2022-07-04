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
     * @var string
     *
     * @ORM\Column(name="locale_product_name", type="string", length=255, nullable=true)
     */
    private $locale_product_name;

    // TODO クラス名等も必要

    /**
     * Set LocaleProductName.
     *
     * @param string $productName
     *
     * @return self
     */
    public function setLocaleProductName(string $productName): self
    {
        $this->locale_product_name = $productName;

        return $this;
    }

    /**
     * Get LocaleProductName.
     *
     * @return string
     */
    public function getLocaleProductName(): string
    {
        return $this->locale_product_name;
    }
}
