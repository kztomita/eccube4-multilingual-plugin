<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual\Entity;

use Eccube\Entity\DeliveryTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocaleDeliveryTime.
 *
 * @ORM\Table(name="plg_ml_locale_delivery_time")
 * @ORM\Entity(repositoryClass="Plugin\MultiLingual\Repository\LocaleDeliveryTimeRepository")
 */
class LocaleDeliveryTime extends AbstractDataLocaleEntity
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
     * @var DeliveryTime
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\DeliveryTime", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    private $DeliveryTime;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_time", type="string", length=255)
     */
    private $delivery_time;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set DeliveryTime.
     *
     * @param DeliveryTime $DeliveryTime
     *
     * @return self
     */
    public function setParentDeliveryTime(DeliveryTime $DeliveryTime): self
    {
        $this->DeliveryTime = $DeliveryTime;

        return $this;
    }

    /**
     * Get DeliveryTime.
     *
     * @return DeliveryTime
     */
    public function getParentDeliveryTime(): DeliveryTime
    {
        return $this->DeliveryTime;
    }

    /**
     * Set delivery time name.
     *
     * @param string $name
     *
     * @return self
     */
    public function setDeliveryTime(string $name): self
    {
        $this->delivery_time = $name;

        return $this;
    }

    /**
     * Get delivery time name.
     *
     * @return string
     */
    public function getDeliveryTime(): string
    {
        return $this->delivery_time;
    }
}
