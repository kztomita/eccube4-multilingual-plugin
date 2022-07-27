<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual\Entity\Master;

use Eccube\Entity\Master\Sex;
use Plugin\MultiLingual\Entity\AbstractMasterLocaleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocaleSex
 *
 * @ORM\Table(name="plg_ml_mtb_locale_sex")
 * @ORM\Entity(repositoryClass="Plugin\MultiLingual\Repository\Master\LocaleSexRepository")
 */
class LocaleSex extends AbstractMasterLocaleEntity
{
    /**
     * @var Sex
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Sex", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    protected $Parent;

    public function getParentClass(): string
    {
        return Sex::class;
    }
}
