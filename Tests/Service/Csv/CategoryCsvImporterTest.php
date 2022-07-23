<?php

namespace Plugin\MultiLingual\Tests\Service\Csv;

use Eccube\Entity\Category;
use Eccube\Service\CsvImportService;
use Eccube\Tests\EccubeTestCase;
use Plugin\MultiLingual\Entity\LocaleCategory;
use Plugin\MultiLingual\Service\Csv\CategoryCsvImporter;

class CategoryCsvImporterTest extends EccubeTestCase
{
    private $categoryRepository;

    private $localeCategoryRepository;

    private $helper;

    public function setUp()
    {
        parent::setUp();

        $em = $this->entityManager;
        $this->categoryRepository = $em->getRepository(Category::class);
        $this->localeCategoryRepository = $em->getRepository(LocaleCategory::class);
        $this->helper = new Helper($this->container);
    }

    public function testCreate()
    {
        $initialCount = count($this->categoryRepository->findAll());

        $csv =<<<END_OF_TEXT
カテゴリID,カテゴリ名,カテゴリ名(en),親カテゴリID,カテゴリ削除フラグ
,新規追加カテゴリ,New Category,,
END_OF_TEXT;

        $importer = $this->container->get(CategoryCsvImporter::class);
        $result = $importer->import($this->helper->createCsvImporterService($csv));
        if (!$result) {
            print_r($importer->getErrors());
        }
        $this->assertTrue($result);

        $this->entityManager->clear();

        // レコード数が増えていること
        $this->assertEquals(
            $initialCount + 1,
            count($this->categoryRepository->findAll())
        );

        $Category = $this->categoryRepository->findOneBy([
            'name' => '新規追加カテゴリ',
        ]);
        $this->assertInstanceOf(Category::class, $Category);
        $this->assertEquals('新規追加カテゴリ', $Category->getName());

        $LocaleCategory = $this->localeCategoryRepository->findOneBy([
            'parent_id' => $Category->getId(),
            'locale' => 'en',
        ]);
        $this->assertInstanceOf(LocaleCategory::class, $LocaleCategory);
        $this->assertEquals('New Category', $LocaleCategory->getName());
    }

    private function createTestRecord(string $name, ?Category $Parent = null)
    {
        $Category = new Category();
        $Category->setName($name);
        $Category->setParent($Parent);
        if ($Parent) {
            $Category->setHierarchy($Parent->getHierarchy() + 1);
        } else {
            $Category->setHierarchy(1);
        }
        $this->entityManager->persist($Category);
        $this->categoryRepository->save($Category);

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

    public function testRemove()
    {
        $Category = $this->createTestRecord('テスト');
        $createdId = $Category->getId();

        $this->assertInstanceOf(
            Category::class,
            $this->categoryRepository->find($createdId)
        );

        $initialCount = count($this->categoryRepository->findAll());

        $csv =<<<END_OF_TEXT
カテゴリID,カテゴリ名,カテゴリ名(en),親カテゴリID,カテゴリ削除フラグ
$createdId,新規追加カテゴリ,New Category,,1
END_OF_TEXT;

        $importer = $this->container->get(CategoryCsvImporter::class);
        $result = $importer->import($this->helper->createCsvImporterService($csv));
        if (!$result) {
            print_r($importer->getErrors());
        }
        $this->assertTrue($result);

        $this->entityManager->clear();

        // レコード数が減っていること
        $this->assertEquals(
            $initialCount - 1,
            count($this->categoryRepository->findAll())
        );

        $this->assertNull($this->categoryRepository->find($createdId));

        $LocaleCategory = $this->localeCategoryRepository->findOneBy([
            'parent_id' => $createdId,
            'locale' => 'en',
        ]);
        $this->assertNull($LocaleCategory);
    }

    public function testUpdate()
    {
        $Category = $this->createTestRecord('テスト');
        $createdId = $Category->getId();

        $initialCount = count($this->categoryRepository->findAll());

        $csv =<<<END_OF_TEXT
カテゴリID,カテゴリ名,カテゴリ名(en),親カテゴリID,カテゴリ削除フラグ
$createdId,野菜,Vegetable,,
END_OF_TEXT;

        $importer = $this->container->get(CategoryCsvImporter::class);
        $result = $importer->import($this->helper->createCsvImporterService($csv));
        if (!$result) {
            print_r($importer->getErrors());
        }
        $this->assertTrue($result);

        $this->entityManager->clear();

        // レコード数が変わっていないこと
        $this->assertEquals(
            $initialCount,
            count($this->categoryRepository->findAll())
        );

        $Category = $this->categoryRepository->find($createdId);
        $this->assertInstanceOf(Category::class, $Category);
        $this->assertEquals('野菜', $Category->getName());

        $LocaleCategory = $this->localeCategoryRepository->findOneBy([
            'parent_id' => $Category->getId(),
            'locale' => 'en',
        ]);
        $this->assertInstanceOf(LocaleCategory::class, $LocaleCategory);
        $this->assertEquals('Vegetable', $LocaleCategory->getName());
    }
}
