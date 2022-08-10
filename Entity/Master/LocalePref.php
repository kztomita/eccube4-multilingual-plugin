<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual\Entity\Master;

use Eccube\Entity\Master\Pref;
use Plugin\MultiLingual\Entity\AbstractMasterLocaleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocalePref
 *
 * @ORM\Table(name="plg_ml_mtb_locale_pref")
 * @ORM\Entity(repositoryClass="Plugin\MultiLingual\Repository\Master\LocalePrefRepository")
 */
class LocalePref extends AbstractMasterLocaleEntity
{
    /**
     * @var Pref
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Pref", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    protected $Parent;

    public function getParentClass(): string
    {
        return Pref::class;
    }
}
