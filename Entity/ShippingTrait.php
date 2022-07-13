<?php

namespace Plugin\MultiLingual\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Eccube\Entity\Shipping;

/**
 * @EntityExtension("Eccube\Entity\Shipping")
 */
trait ShippingTrait
{
    /**
     * @var ?string
     *
     * @ORM\Column(name="locale_delivery_name", type="string", length=255, nullable=true)
     */
    private $locale_delivery_name;

    /**
     * @var ?string
     *
     * @ORM\Column(name="locale_delivery_time", type="string", length=255, nullable=true)
     */
    private $locale_delivery_time;

    /**
     * Set locale delivery name.
     *
     * @param ?string $name
     *
     * @return self
     */
    public function setLocaleDeliveryName(?string $name): self
    {
        $this->locale_delivery_name = $name;

        return $this;
    }

    /**
     * Get locale delivery name.
     *
     * @return ?string
     */
    public function getLocaleDeliveryName(): ?string
    {
        return $this->locale_delivery_name;
    }

    /**
     * Set locale delivery time.
     *
     * @param ?string $name
     *
     * @return self
     */
    public function setLocaleDeliveryTime(?string $time): self
    {
        $this->locale_delivery_time = $time;

        return $this;
    }

    /**
     * Get locale delivery time.
     *
     * @return ?string
     */
    public function getLocaleDeliveryTime(): ?string
    {
        return $this->locale_delivery_time;
    }
}
