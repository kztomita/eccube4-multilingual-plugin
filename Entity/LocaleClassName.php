<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual\Entity;

use Eccube\Entity\ClassName;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocaleClassName.
 *
 * @ORM\Table(name="plg_ml_locale_class_name")
 * @ORM\Entity(repositoryClass="Plugin\MultiLingual\Repository\LocaleClassNameRepository")
 */
class LocaleClassName extends AbstractDataLocaleEntity
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
     * @var ClassName
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\ClassName", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    private $ClassName;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set ClassName.
     *
     * @param ClassName $ClassName
     *
     * @return self
     */
    public function setClassName(ClassName $ClassName): self
    {
        $this->ClassName = $ClassName;

        return $this;
    }

    /**
     * Get ClassName.
     *
     * @return ClassName
     */
    public function getClassName(): ClassName
    {
        return $this->ClassName;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
