<?php

namespace Plugin\MultiLingual\Tests\Service\Csv;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Category;
use Eccube\Entity\Product;
use Eccube\Service\CsvImportService;
use Plugin\MultiLingual\Entity\LocaleCategory;
use Plugin\MultiLingual\Entity\LocaleProduct;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Helper
{
   /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(ContainerInterface $container)
    {
        $this->eccubeConfig = $container->get(EccubeConfig::class);
        $this->entityManager = $container->get('doctrine')->getManager();
    }

    public function createCsvFile(string $contents)
    {
        $tmp = tmpfile();
        fwrite($tmp, $contents);
        rewind($tmp);
        $meta = stream_get_meta_data($tmp);
        $file = new \SplFileObject($meta['uri']);
        return $file;
    }

    public function createCsvImporterService(string $contents)
    {
        $importerService = new CsvImportService(
            self::createCsvFile($contents),
            $this->eccubeConfig['eccube_csv_import_delimiter'],
            $this->eccubeConfig['eccube_csv_import_enclosure']
        );
        $importerService->setHeaderRowNumber(0);
        return $importerService;
    }

    /**
     * テスト用のカテゴリレコードを作成
     */
    public function createCategory(string $name, ?Category $Parent = null)
    {
        $em = $this->entityManager;
        $categoryRepository = $em->getRepository(Category::class);

        $Category = new Category();
        $Category->setName($name);
        $Category->setParent($Parent);
        if ($Parent) {
            $Category->setHierarchy($Parent->getHierarchy() + 1);
        } else {
            $Category->setHierarchy(1);
        }
        $this->entityManager->persist($Category);
        $categoryRepository->save($Category);

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            $LocaleCategory = new LocaleCategory();
            $LocaleCategory->setParentId($Category->getId());
            $LocaleCategory->setCategory($Category);
            $LocaleCategory->setName($name . ' - ' . $locale);
            $LocaleCategory->setLocale($locale);
            $this->entityManager->persist($LocaleCategory);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return $Category;
    }

    public function createProduct(string $name)
    {
        $em = $this->entityManager;

        $Product = new Product();
        $Product->setName($name);
        $this->entityManager->persist($Product);
        $this->entityManager->flush();

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            $LocaleProduct = new LocaleProduct();
            $LocaleProduct->setParentId($Product->getId());
            $LocaleProduct->setProduct($Product);
            $LocaleProduct->setName($name . ' - ' . $locale);
            $LocaleProduct->setLocale($locale);
            $this->entityManager->persist($LocaleProduct);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return $Product;
    }

}
