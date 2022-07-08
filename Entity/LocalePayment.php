<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual\Entity;

use Eccube\Entity\Payment;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocalePayment.
 *
 * @ORM\Table(name="plg_ml_locale_payment")
 * @ORM\Entity(repositoryClass="Plugin\MultiLingual\Repository\LocalePaymentRepository")
 */
class LocalePayment extends AbstractDataLocaleEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Payment
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Payment", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    private $Payment;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_method", type="string", length=255, nullable=true)
     */
    private $method;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set Payment.
     *
     * @param Payment $Payment
     *
     * @return self
     */
    public function setPayment(Payment $Payment): self
    {
        $this->Payment = $Payment;

        return $this;
    }

    /**
     * Get Payment.
     *
     * @return Payment
     */
    public function getPayment(): Payment
    {
        return $this->Payment;
    }

    /**
     * Set payment method name.
     *
     * @param ?string $method
     *
     * @return self
     */
    public function setMethod(?string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get payment method name.
     *
     * @return ?string
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }
}
