<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual\Entity\Master;

use Eccube\Entity\Master\ProductListOrderBy;
use Plugin\MultiLingual\Entity\AbstractMasterLocaleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocaleProductListOrderBy.
 *
 * @ORM\Table(name="plg_ml_mtb_locale_product_list_order_by")
 * @ORM\Entity(repositoryClass="Plugin\MultiLingual\Repository\Master\LocaleProductListOrderByRepository")
 */
class LocaleProductListOrderBy extends AbstractMasterLocaleEntity
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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set parent.
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
     * Get parent.
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
}
