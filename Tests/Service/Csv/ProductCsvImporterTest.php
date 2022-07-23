<?php

namespace Plugin\MultiLingual\Tests\Service\Csv;

use Eccube\Entity\Product;
use Eccube\Service\CsvImportService;
use Eccube\Tests\EccubeTestCase;
use Plugin\MultiLingual\Entity\LocaleProduct;
use Plugin\MultiLingual\Service\Csv\ProductCsvImporter;

class ProductCsvImporterTest extends EccubeTestCase
{
    private $productRepository;

    private $localeProductRepository;

    private $helper;

    public function setUp()
    {
        parent::setUp();

        $em = $this->entityManager;
        $this->productRepository = $em->getRepository(Product::class);
        $this->localeProductRepository = $em->getRepository(LocaleProduct::class);
        $this->helper = new Helper($this->container);
    }

    public function testCreate()
    {
        $initialCount = count($this->productRepository->findAll());

        // 新規作成のテスト
        $csv =<<<END_OF_TEXT
商品ID,公開ステータス(ID),商品名,商品名(en),ショップ用メモ欄,商品説明(一覧),商品説明(一覧)(en),商品説明(詳細),商品説明(詳細)(en),検索ワード,フリーエリア,フリーエリア(en),商品削除フラグ,商品画像,商品カテゴリ(ID),タグ(ID),販売種別(ID),規格分類1(ID),規格分類2(ID),発送日目安(ID),商品コード,在庫数,在庫数無制限フラグ,販売制限数,通常価格,販売価格,送料,税率
,1,テスト,test,メモです。,テスト用の商品,テスト用の商品(en),テスト用の商品になります,テスト用の商品になります(en),検索ワード,フリーエリア,フリーエリア(en),0,,2,,1,,,,CD-TEST01,,1,,3000,3000,100,10
END_OF_TEXT;

        $importer = $this->container->get(ProductCsvImporter::class);
        $result = $importer->import($this->helper->createCsvImporterService($csv));
        if (!$result) {
            print_r($importer->getErrors());
        }
        $this->assertTrue($result);

        $this->entityManager->clear();

        // レコード数が増えていること
        $this->assertEquals(
            $initialCount + 1,
            count($this->productRepository->findAll())
        );

        $Product = $this->productRepository->findOneBy([
            'name' => 'テスト',
        ]);
        $this->assertInstanceOf(Product::class, $Product);
        $this->assertEquals('テスト', $Product->getName());
        $this->assertEquals('メモです。', $Product->getNote());
        $this->assertEquals('テスト用の商品', $Product->getDescriptionList());
        $this->assertEquals('テスト用の商品になります', $Product->getDescriptionDetail());
        $this->assertEquals('検索ワード', $Product->getSearchWord());
        $this->assertEquals('フリーエリア', $Product->getFreeArea());
        //$this->assertEquals('CD-TEST01', $Product->getCode());

        $createdId = $Product->getId();

        $LocaleProduct = $this->localeProductRepository->findOneBy([
            'parent_id' => $Product->getId(),
            'locale' => 'en',
        ]);
        $this->assertInstanceOf(LocaleProduct::class, $LocaleProduct);
        $this->assertEquals('test', $LocaleProduct->getName());
        $this->assertEquals('テスト用の商品(en)', $LocaleProduct->getDescriptionList());
        $this->assertEquals('テスト用の商品になります(en)', $LocaleProduct->getDescriptionDetail());
        $this->assertEquals('フリーエリア(en)', $LocaleProduct->getFreeArea());

    }

    private function createTestRecord(string $name)
    {
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

    public function testRemove()
    {
        $Product = $this->createTestRecord('テスト');
        $createdId = $Product->getId();

        $this->assertInstanceOf(
            Product::class,
            $this->productRepository->find($createdId)
        );

        $initialCount = count($this->productRepository->findAll());

        // 削除のテスト
        $csv =<<<END_OF_TEXT
商品ID,公開ステータス(ID),商品名,商品名(en),ショップ用メモ欄,商品説明(一覧),商品説明(一覧)(en),商品説明(詳細),商品説明(詳細)(en),検索ワード,フリーエリア,フリーエリア(en),商品削除フラグ,商品画像,商品カテゴリ(ID),タグ(ID),販売種別(ID),規格分類1(ID),規格分類2(ID),発送日目安(ID),商品コード,在庫数,在庫数無制限フラグ,販売制限数,通常価格,販売価格,送料,税率
$createdId,1,テスト,test,メモです。,テスト用の商品,テスト用の商品(en),テスト用の商品になります,テスト用の商品になります(en),検索ワード,フリーエリア,フリーエリア(en),1,,2,,1,,,,CD-TEST01,,1,,3000,3000,100,10
END_OF_TEXT;

        $importer = $this->container->get(ProductCsvImporter::class);
        $result = $importer->import($this->helper->createCsvImporterService($csv));
        if (!$result) {
            print_r($importer->getErrors());
        }
        $this->assertTrue($result);

        $this->entityManager->clear();

        // レコード数が減っていること
        $this->assertEquals(
            $initialCount - 1,
            count($this->productRepository->findAll())
        );

        $Product = $this->productRepository->find($createdId);
        $this->assertNull($Product);

        $LocaleProduct = $this->localeProductRepository->findOneBy([
            'parent_id' => $createdId,
            'locale' => 'en',
        ]);
        $this->assertNull($LocaleProduct);
    }

    public function testUpdate()
    {
        $Product = $this->createTestRecord('テスト');
        $createdId = $Product->getId();

        $initialCount = count($this->productRepository->findAll());

        $csv =<<<END_OF_TEXT
商品ID,公開ステータス(ID),商品名,商品名(en),ショップ用メモ欄,商品説明(一覧),商品説明(一覧)(en),商品説明(詳細),商品説明(詳細)(en),検索ワード,フリーエリア,フリーエリア(en),商品削除フラグ,商品画像,商品カテゴリ(ID),タグ(ID),販売種別(ID),規格分類1(ID),規格分類2(ID),発送日目安(ID),商品コード,在庫数,在庫数無制限フラグ,販売制限数,通常価格,販売価格,送料,税率
$createdId,1,人参,Carrot,メモです。,テスト用の商品,テスト用の商品(en),テスト用の商品になります,テスト用の商品になります(en),検索ワード,フリーエリア,フリーエリア(en),0,,2,,1,,,,CD-TEST01,,1,,3000,3000,100,10
END_OF_TEXT;

        $importer = $this->container->get(ProductCsvImporter::class);
        $result = $importer->import($this->helper->createCsvImporterService($csv));
        if (!$result) {
            print_r($importer->getErrors());
        }
        $this->assertTrue($result);

        $this->entityManager->clear();

        // レコード数が変わっていないこと
        $this->assertEquals(
            $initialCount,
            count($this->productRepository->findAll())
        );

        $Product = $this->productRepository->find($createdId);
        $this->assertInstanceOf(Product::class, $Product);
        $this->assertEquals('人参', $Product->getName());

        $LocaleProduct = $this->localeProductRepository->findOneBy([
            'parent_id' => $Product->getId(),
            'locale' => 'en',
        ]);
        $this->assertInstanceOf(LocaleProduct::class, $LocaleProduct);
        $this->assertEquals('Carrot', $LocaleProduct->getName());
    }
}

