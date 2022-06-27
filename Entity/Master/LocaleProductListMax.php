<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual\Entity\Master;

use Eccube\Entity\Master\ProductListMax;
use Plugin\MultiLingual\Entity\AbstractMasterLocaleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocaleProductListMax.
 *
 * @ORM\Table(name="plg_ml_mtb_locale_product_list_max")
 * @ORM\Entity(repositoryClass="Plugin\MultiLingual\Repository\Master\LocaleProductListMaxRepository")
 */
class LocaleProductListMax extends AbstractMasterLocaleEntity
{
    /**
     * @var ProductListMax
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\ProductListMax", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    protected $Parent;

    public function getParentClass(): string
    {
        return ProductListMax::class;
    }
}
