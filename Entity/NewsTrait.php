<?php

namespace Plugin\MultiLingual\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Eccube\Entity\News;

/**
 * @EntityExtension("Eccube\Entity\News")
 */
trait NewsTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=10)
     */
    private $locale;

    /**
     * Set locale name.
     *
     * @param string $locale
     *
     * @return News
     */
    public function setLocale(string $locale): News
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
