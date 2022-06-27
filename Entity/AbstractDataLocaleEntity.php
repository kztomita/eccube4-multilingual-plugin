<?php

namespace Plugin\MultiLingual\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;

/**
 * 各Localeでのテキスト情報を格納するEntityの基底クラス。
 * データテーブル(dtb_*)のEntityのLocale Entityは本クラスを継承する。
 * AbstractMasterLocaleEntityとはparent_idの型(サイズ)が異なる。
 *
 * @MappedSuperclass
 */
abstract class AbstractDataLocaleEntity extends AbstractLocaleEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="parent_id", type="integer", options={"unsigned":true})
     */
    protected $parent_id;

    /**
     * Set parentId.
     *
     * @param int $parentId
     *
     * @return AbstractLocaleEntity
     */
    public function setParentId(int $parentId): AbstractLocaleEntity
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
}
