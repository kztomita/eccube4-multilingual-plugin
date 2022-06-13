<?php

namespace Plugin\MultiLingual\Entity;

use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Category;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocaleCategory.
 *
 * @ORM\Table(name="plg_locale_category")
 * @ORM\Entity(repositoryClass="Plugin\MultiLingual\Repository\LocaleCategoryRepository")
 */
class LocaleCategory extends AbstractEntity
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
     * @var \Eccube\Entity\Category
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Category", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * })
     */
    private $Category;

    /**
     * @var string
     *
     * @ORM\Column(name="category_name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=10)
     */
    private $locale;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set category.
     *
     * @param \Eccube\Entity\Category|null $category
     *
     * @return LocaleCategory
     */
    public function setCategory(Category $category = null)
    {
        $this->Category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return \Eccube\Entity\Category|null
     */
    public function getCategory()
    {
        return $this->Category;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return LocaleCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set locale name.
     *
     * @param string $locale
     *
     * @return LocaleCategory
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale name.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
