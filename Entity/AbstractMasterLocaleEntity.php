<?php

namespace Plugin\MultiLingual\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Eccube\Entity\AbstractEntity;

/**
 * 各Localeでのテキスト情報を格納するEntityの基底クラス。
 * マスターテーブル(mtb_*)のEntityのLocale Entityは本クラスを継承する。
 * AbstractDataLocaleEntityとはparent_idの型(サイズ)が異なる。
 *
 * @MappedSuperclass
 */
abstract class AbstractMasterLocaleEntity extends AbstractLocaleEntity
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

    /* $Parentの型は具象クラスによって異なるので、サブクラスで宣言する。 */

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
     * $Parentのクラス名を返す。
     *
     * @return string
     */
    abstract public function getParentClass(): string;

    /**
     * Set parent.
     *
     * @param AbstractEntity $parent
     * @return self
     */
    public function setParent(AbstractEntity $parent): self
    {
        $parentClass = $this->getParentClass();
        if (!($parent instanceof $parentClass)) {
            throw new \InvalidArgumentException('$parent is not ' .$parentClass);
        }

        $this->Parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return AbstractEntity
     */
    public function getParent(): AbstractEntity
    {
        return $this->Parent;
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
