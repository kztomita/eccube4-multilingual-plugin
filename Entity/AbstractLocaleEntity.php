<?php

namespace Plugin\MultiLingual\Entity;

use Eccube\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;

/**
 * 各Localeでのテキスト情報を格納するEntityの基底クラス。
 *
 * @MappedSuperclass
 */
class AbstractLocaleEntity extends AbstractEntity
{
    /*
     * 親クラスでのカラム定義については以下を参照。
     * https://www.doctrine-project.org/projects/doctrine-orm/en/2.11/reference/inheritance-mapping.html
     */

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=10)
     */
    protected $locale;

    /**
     * Set locale name.
     *
     * @param string $locale
     *
     * @return AbstractLocaleEntity
     */
    public function setLocale(string $locale): AbstractLocaleEntity
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