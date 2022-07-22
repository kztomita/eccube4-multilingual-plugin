<?php

namespace Plugin\MultiLingual\Tests\Service\Csv;

use Eccube\Entity\Category;
use Eccube\Service\CsvImportService;
use Eccube\Tests\EccubeTestCase;
use Plugin\MultiLingual\Entity\LocaleCategory;
use Plugin\MultiLingual\Service\Csv\CategoryCsvImporter;

class CategoryCsvImporterTest extends EccubeTestCase
{
    private function createCsvFile(string $contents)
    {
        $tmp = tmpfile();
        fwrite($tmp, $contents);
        rewind($tmp);
        $meta = stream_get_meta_data($tmp);
        $file = new \SplFileObject($meta['uri']);
        return $file;
    }

    public function test()
    {
        $container = self::$kernel->getContainer();

        $em = $container->get('doctrine.orm.entity_manager');

        $categoryRepository = $em->getRepository(Category::class);
        $localeCategoryRepository = $em->getRepository(LocaleCategory::class);

        $initialCount = count($categoryRepository->findAll());

        $csv =<<<END_OF_TEXT
カテゴリID,カテゴリ名,カテゴリ名(en),カテゴリ名(cn),親カテゴリID,カテゴリ削除フラグ
,新規追加カテゴリ,New Category,,,
1,ジェラート2,Gelato2,,,
END_OF_TEXT;

        $file = $this->createCsvFile($csv);
        $data = new CsvImportService($file, $this->eccubeConfig['eccube_csv_import_delimiter'], $this->eccubeConfig['eccube_csv_import_enclosure']);
        $data->setHeaderRowNumber(0);

        $importer = $container->get(CategoryCsvImporter::class);
        $result = $importer->import($data);
        if (!$result) {
            print_r($importer->getErrors());
        }
        $this->assertTrue($result);

        // 新規作成カテゴリのテスト
        $this->assertEquals(
            $initialCount + 1,
            count($categoryRepository->findAll())
        );

        $category = $categoryRepository->findOneBy([
            'name' => '新規追加カテゴリ',
        ]);
        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('新規追加カテゴリ', $category->getName());

        $localeCategory = $localeCategoryRepository->findOneBy([
            'parent_id' => $category->getId(),
            'locale' => 'en',
        ]);
        $this->assertInstanceOf(LocaleCategory::class, $localeCategory);
        $this->assertEquals('New Category', $localeCategory->getName());

        // 更新カテゴリのテスト
        $category = $categoryRepository->find(1);
        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('ジェラート2', $category->getName());

        $localeCategory = $localeCategoryRepository->findOneBy([
            'parent_id' => $category->getId(),
            'locale' => 'en',
        ]);
        $this->assertInstanceOf(LocaleCategory::class, $localeCategory);
        $this->assertEquals('Gelato2', $localeCategory->getName());
    }
}
