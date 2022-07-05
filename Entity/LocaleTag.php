<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual\Entity;

use Eccube\Entity\Tag;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocaleTag.
 *
 * @ORM\Table(name="plg_ml_locale_tag")
 * @ORM\Entity(repositoryClass="Plugin\MultiLingual\Repository\LocaleTagRepository")
 */
class LocaleTag extends AbstractDataLocaleEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Tag
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Tag", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     */
    private $Tag;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set tag.
     *
     * @param Tag $tag
     *
     * @return self
     */
    public function setTag(Tag $tag): self
    {
        $this->Tag = $tag;

        return $this;
    }

    /**
     * Get tag.
     *
     * @return Tag
     */
    public function getTag(): Tag
    {
        return $this->Tag;
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
