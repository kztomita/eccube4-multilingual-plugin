<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual\Entity;

use Eccube\Entity\Category;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocaleCategory.
 *
 * @ORM\Table(name="plg_ml_locale_category")
 * @ORM\Entity(repositoryClass="Plugin\MultiLingual\Repository\LocaleCategoryRepository")
 */
class LocaleCategory extends AbstractDataLocaleEntity
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
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Category", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    private $Category;

    /**
     * @var string
     *
     * @ORM\Column(name="category_name", type="string", length=255)
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
     * Set category.
     *
     * @param Category $category
     *
     * @return LocaleCategory
     */
    public function setCategory(Category $category): LocaleCategory
    {
        $this->Category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->Category;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return LocaleCategory
     */
    public function setName(string $name): LocaleCategory
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
