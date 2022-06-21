<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual\Entity\Master;

use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Master\ProductListOrderBy;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocaleProductListOrderBy.
 *
 * @ORM\Table(name="plg_locale_product_list_order_by")
 * @ORM\Entity(repositoryClass="Plugin\MultiLingual\Repository\Master\LocaleProductListOrderByRepository")
 */
class LocaleProductListOrderBy extends AbstractEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="smallint", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="parent_id", type="smallint", options={"unsigned":true})
     */
    private $parent_id;

    /**
     * @var ProductListOrderBy
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\ProductListOrderBy", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    private $Parent;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=10)
     */
    private $locale;

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
        return 'parent_id';
    }

    /**
     * Set parentId.
     *
     * @param int $parentId
     *
     * @return LocaleProductListOrderBy
     */
    public function setParentId(int $parentId): LocaleProductListOrderBy
    {
        $this->parent_id = $parentId;

        return $this;
    }

    /**
     * Get parentId.
     *
     * @return int
     */
    public function getParentId(): int
    {
        return $this->parent_id;
    }

    /**
     * Set category.
     *
     * @param ProductListOrderBy $parent
     * @return LocaleProductListOrderBy
     */
    public function setParent(ProductListOrderBy $parent): LocaleProductListOrderBy
    {
        $this->Parent = $parent;

        return $this;
    }

    /**
     * Get category.
     *
     * @return ProductListOrderBy
     */
    public function getParent(): ProductListOrderBy
    {
        return $this->Parent;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return LocaleProductListOrderBy
     */
    public function setName(string $name): LocaleProductListOrderBy
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

    /**
     * Set locale name.
     *
     * @param string $locale
     *
     * @return LocaleProductListOrderBy
     */
    public function setLocale(string $locale): LocaleProductListOrderBy
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale name.
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }
}
