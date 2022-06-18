<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual\Entity;

use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Product;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LocaleProduct.
 *
 * @ORM\Table(name="plg_locale_product")
 * @ORM\Entity(repositoryClass="Plugin\MultiLingual\Repository\LocaleProductRepository")
 */
class LocaleProduct extends AbstractEntity
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
     * @var int
     *
     * @ORM\Column(name="product_id", type="integer", options={"unsigned":true})
     */
    private $product_id;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Product", inversedBy="Locales")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     * })
     */
    private $Product;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description_list", type="string", length=4000, nullable=true)
     */
    private $description_list;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description_detail", type="string", length=4000, nullable=true)
     */
    private $description_detail;

    /**
     * @var string|null
     *
     * @ORM\Column(name="free_area", type="text", nullable=true)
     */
    private $free_area;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=10)
     */
    private $locale;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * 親EntityのIDを格納するカラムの名前。
     *
     * @return string
     */
    public static function getParentColumn(): string
    {
        return 'product_id';
    }

    /**
     * Set productId.
     *
     * @param int $productId
     *
     * @return LocaleProduct
     */
    public function setProductId(int $productId): LocaleProduct
    {
        $this->product_id = $productId;

        return $this;
    }

    /**
     * Get productId.
     *
     * @return int
     */
    public function getProductId(): int
    {
        return $this->product_id;
    }

    /**
     * Set product.
     *
     * @param Product $product
     *
     * @return LocaleProduct
     */
    public function setProduct(Product $product): LocaleProduct
    {
        $this->Product = $product;

        return $this;
    }

    /**
     * Get product.
     *
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->Product;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return LocaleProduct
     */
    public function setName(string $name): LocaleProduct
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

    /**
     * Set descriptionList.
     *
     * @param string|null $text
     *
     * @return LocaleProduct
     */
    public function setDescriptionList(?string $text): LocaleProduct
    {
        $this->description_list = $text;

        return $this;
    }

    /**
     * Get descriptionList.
     *
     * @return string|null
     */
    public function getDescriptionList(): ?string
    {
        return $this->description_list;
    }

    /**
     * Set descriptionDetail.
     *
     * @param string|null $text
     *
     * @return LocaleProduct
     */
    public function setDescriptionDetail(?string $text): LocaleProduct
    {
        $this->description_detail = $text;

        return $this;
    }

    /**
     * Get descriptionDetail.
     *
     * @return string|null
     */
    public function getDescriptionDetail(): ?string
    {
        return $this->description_detail;
    }

    /**
     * Set freeArea.
     *
     * @param string|null $freeArea
     *
     * @return LocaleProduct
     */
    public function setFreeArea(?string $freeArea = null): LocaleProduct
    {
        $this->free_area = $freeArea;

        return $this;
    }

    /**
     * Get freeArea.
     *
     * @return string|null
     */
    public function getFreeArea(): ?string
    {
        return $this->free_area;
    }

    /**
     * Set locale name.
     *
     * @param string $locale
     *
     * @return LocaleProduct
     */
    public function setLocale(string $locale): LocaleProduct
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
