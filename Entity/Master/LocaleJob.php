<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual\Entity\Master;

use Eccube\Entity\Master\Job;
use Plugin\MultiLingual\Entity\AbstractMasterLocaleEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocaleJob
 *
 * @ORM\Table(name="plg_ml_mtb_locale_job")
 * @ORM\Entity(repositoryClass="Plugin\MultiLingual\Repository\Master\LocaleJobRepository")
 */
class LocaleJob extends AbstractMasterLocaleEntity
{
    /**
     * @var Job
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Job", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    protected $Parent;

    public function getParentClass(): string
    {
        return Job::class;
    }
}
