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
class LocaleCategory extends AbstractLocaleEntity
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
     * @var int
     *
     * @ORM\Column(name="category_id", type="integer", options={"unsigned":true})
     */
    private $category_id;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Category", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="category_id", referencedColumnName="id")
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
     * 親EntityのIDを格納するカラムの名前。
     *
     * @return string
     */
    public static function getParentColumn(): string
    {
        return 'category_id';
    }

    /**
     * Set categoryId.
     *
     * @param int $categoryId
     *
     * @return LocaleCategory
     */
    public function setCategoryId(int $categoryId): LocaleCategory
    {
        $this->category_id = $categoryId;

        return $this;
    }

    /**
     * Get categoryId.
     *
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->category_id;
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
