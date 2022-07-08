<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual\Entity;

use Eccube\Entity\Delivery;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocaleDelivery.
 *
 * @ORM\Table(name="plg_ml_locale_delivery")
 * @ORM\Entity(repositoryClass="Plugin\MultiLingual\Repository\LocaleDeliveryRepository")
 */
class LocaleDelivery extends AbstractDataLocaleEntity
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
     * @var Delivery
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Delivery", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    private $Delivery;

    /**
     * @var ?string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var ?string
     *
     * @ORM\Column(name="service_name", type="string", length=255, nullable=true)
     */
    private $service_name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set Delivery.
     *
     * @param Delivery $Delivery
     *
     * @return self
     */
    public function setDelivery(Delivery $Delivery): self
    {
        $this->Delivery = $Delivery;

        return $this;
    }

    /**
     * Get Delivery.
     *
     * @return Delivery
     */
    public function getDelivery(): Delivery
    {
        return $this->Delivery;
    }

    /**
     * Set name.
     *
     * @param ?string $name
     *
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return ?string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set service_name.
     *
     * @param ?string $service_name
     *
     * @return self
     */
    public function setServiceName(?string $service_name): self
    {
        $this->service_name = $service_name;

        return $this;
    }

    /**
     * Get service_name.
     *
     * @return ?string
     */
    public function getServiceName(): ?string
    {
        return $this->service_name;
    }
}
