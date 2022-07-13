<?php

namespace Plugin\MultiLingual\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Eccube\Entity\Order;

/**
 * @EntityExtension("Eccube\Entity\Order")
 */
trait OrderTrait
{
    /**
     * @var ?string
     *
     * @ORM\Column(name="locale_payment_method", type="string", length=255, nullable=true)
     */
    private $locale_payment_method;

    /**
     * Set locale payment method name.
     *
     * @param ?string $method
     *
     * @return self
     */
    public function setLocalePaymentMethod(?string $method): self
    {
        $this->locale_payment_method = $method;

        return $this;
    }

    /**
     * Get locale payment method name.
     *
     * @return ?string
     */
    public function getLocalePaymentMethod(): ?string
    {
        return $this->locale_payment_method;
    }
}
