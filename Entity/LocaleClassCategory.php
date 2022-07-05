<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual\Entity;

use Eccube\Entity\ClassCategory;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocaleClassCategory.
 *
 * @ORM\Table(name="plg_ml_locale_class_category")
 * @ORM\Entity(repositoryClass="Plugin\MultiLingual\Repository\LocaleClassCategoryRepository")
 */
class LocaleClassCategory extends AbstractDataLocaleEntity
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
     * @var ClassCategory
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\ClassCategory", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    private $ClassCategory;

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
     * @param ClassCategory $ClassCategory
     *
     * @return self
     */
    public function setClassCategory(ClassCategory $ClassCategory): self
    {
        $this->ClassCategory = $ClassCategory;

        return $this;
    }

    /**
     * Get ClassName.
     *
     * @return ClassCategory
     */
    public function getClassCategory(): ClassCategory
    {
        return $this->ClassCategory;
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
