<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual\Entity\Master;

use Eccube\Entity\Master\CustomerOrderStatus;
use Plugin\MultiLingual\Entity\AbstractMasterLocaleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocaleCustomerOrderStatus.
 *
 * @ORM\Table(name="plg_ml_mtb_locale_customer_order_status")
 * @ORM\Entity(repositoryClass="Plugin\MultiLingual\Repository\Master\LocaleCustomerOrderStatusRepository")
 */
class LocaleCustomerOrderStatus extends AbstractMasterLocaleEntity
{
    /**
     * @var CustomerOrderStatus
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\CustomerOrderStatus", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    protected $Parent;

    public function getParentClass(): string
    {
        return CustomerOrderStatus::class;
    }
}
