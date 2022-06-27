<?php

namespace Plugin\MultiLingual\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;

/**
 * 各Localeでのテキスト情報を格納するEntityの基底クラス。
 * マスターテーブル(mtb_*)のEntityのLocale Entityは本クラスを継承する。
 * AbstractDataLocaleEntityとはparent_idの型(サイズ)が異なる。
 *
 * @MappedSuperclass
 */
class AbstractMasterLocaleEntity extends AbstractLocaleEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="smallint", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="parent_id", type="smallint", options={"unsigned":true})
     */
    protected $parent_id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

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

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
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
