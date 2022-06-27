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
     * @var ProductListOrderBy
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\ProductListOrderBy", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    protected $Parent;

    public function getParentClass(): string
    {
        return ProductListOrderBy::class;
    }
}
