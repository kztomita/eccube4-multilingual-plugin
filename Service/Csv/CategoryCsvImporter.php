<?php

namespace Plugin\MultiLingual\Service\Csv;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Category;
use Eccube\Repository\CategoryRepository;
use Eccube\Service\CsvImportService;
use Eccube\Util\CacheUtil;
use Eccube\Util\StringUtil;
use Plugin\MultiLingual\Entity\LocaleCategory;
use Plugin\MultiLingual\Repository\LocaleCategoryRepository;

class CategoryCsvImporter
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

   /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var LocaleCategoryRepository
     */
    private $localeCategoryRepository;

    /**
     * @var CacheUtil
     */
    private $cacheUtil;

    /**
     * @var string[]
     */
    private $errors = [];

    /**
     * @var int
     */
    private $lineNo = 0;

    /**
     * @var array<string, string>
     */
    private $headerByKey = [];

    /**
     * PaymentRegisterExtension constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param EccubeConfig $eccubeConfig
     * @param CategoryRepository $categoryRepository
     * @param LocaleCategoryRepository $localeCategoryRepository
     * @param CacheUtil $cacheUtil
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        EccubeConfig $eccubeConfig,
        CategoryRepository $categoryRepository,
        LocaleCategoryRepository $localeCategoryRepository,
        CacheUtil $cacheUtil
    )
    {
        $this->entityManager = $entityManager;
        $this->eccubeConfig = $eccubeConfig;
        $this->categoryRepository = $categoryRepository;
        $this->localeCategoryRepository = $localeCategoryRepository;
        $this->cacheUtil = $cacheUtil;
    }

    private function addError(string $message)
    {
        $this->errors[] = $message;
    }

    private function addErrorWithLineNo(string $message)
    {
        $this->errors[] = $this->lineNo . '行目:' . $message;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return count($this->errors) ? true : false;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * CSVファイルのインポート処理
     *
     * @param CsvImportService $importService
     * @return bool
     */
    public function import(CsvImportService $importService): bool
    {
        $headers = $this->getCsvHeader();

        $getId = function ($item) {
            return $item['id'];
        };
        $requireHeader = array_keys(array_map($getId, array_filter($headers, function ($value) {
            return $value['required'];
        })));

        $this->headerByKey = array_flip(array_map($getId, $headers));

        $columnHeaders = $importService->getColumnHeaders();
        if (count(array_diff($requireHeader, $columnHeaders)) > 0) {
            $this->addError(trans('admin.common.csv_invalid_format'));
            return false;
        }

        $size = count($importService);
        if ($size < 1) {
            $this->addError(trans('admin.common.csv_invalid_no_data'));
            return false;
        }
        $this->entityManager->getConfiguration()->setSQLLogger(null);
        $this->entityManager->getConnection()->beginTransaction();
        try {
            // CSVファイルの登録処理
            $this->lineNo = 1;
            foreach ($importService as $row) {
                if (!$this->importRow($row)) {
                    $this->entityManager->rollback();
                    return false;
                }
                $this->lineNo++;
            }

            $this->entityManager->getConnection()->commit();

            $this->cacheUtil->clearDoctrineCache();
        } catch(\Exception $e) {
            $this->addError($e->getMessage());
            $this->entityManager->rollback();
            return false;
        }

        return true;
    }

    /**
     * 行のインポート処理。
     * メソッド内からlineNo, headerByKeyを参照するので設定しておくこと。
     *
     * @param array $row
     * @return bool
     * @throws \Doctrine\DBAL\Exception\DriverException
     */
    private function importRow(array $row): bool
    {
        $headerByKey = $this->headerByKey;

        $Category = new Category();
        if (isset($row[$headerByKey['id']]) && strlen($row[$headerByKey['id']]) > 0) {
            if (!preg_match('/^\d+$/', $row[$headerByKey['id']])) {
                $this->addErrorWithLineNo('カテゴリIDが存在しません。');
                return false;
            }
            $Category = $this->categoryRepository->find($row[$headerByKey['id']]);
            if (!$Category) {
                $this->addErrorWithLineNo('更新対象のカテゴリIDが存在しません。新規登録の場合は、カテゴリIDの値を空で登録してください。');
                return false;
            }
            if ($row[$headerByKey['id']] == $row[$headerByKey['parent_category_id']]) {
                $this->addErrorWithLineNo('カテゴリIDと親カテゴリIDが同じです。');
                return false;
            }
        }

        if (isset($row[$headerByKey['category_del_flg']]) && StringUtil::isNotBlank($row[$headerByKey['category_del_flg']])) {
            if (StringUtil::trimAll($row[$headerByKey['category_del_flg']]) == 1) {
                if ($Category->getId()) {
                    log_info('カテゴリ削除開始', [$Category->getId()]);
                    try {
                        $this->categoryRepository->delete($Category);
                        log_info('カテゴリ削除完了', [$Category->getId()]);
                    } catch (ForeignKeyConstraintViolationException $e) {
                        log_info('カテゴリ削除エラー', [$Category->getId(), $e]);
                        $message = trans('admin.common.delete_error_foreign_key', ['%name%' => $Category->getName()]);
                        $this->addErrorWithLineNo($message);
                        return false;
                    }
                }

                return true;
            }
        }

        if (!isset($row[$headerByKey['category_name']]) || StringUtil::isBlank($row[$headerByKey['category_name']])) {
            $this->addErrorWithLineNo('カテゴリ名が設定されていません。');
            return false;
        } else {
            $Category->setName(StringUtil::trimAll($row[$headerByKey['category_name']]));
        }

        $ParentCategory = null;
        if (isset($row[$headerByKey['parent_category_id']]) && StringUtil::isNotBlank($row[$headerByKey['parent_category_id']])) {
            if (!preg_match('/^\d+$/', $row[$headerByKey['parent_category_id']])) {
                $this->addErrorWithLineNo('親カテゴリIDが存在しません。');
                return false;
            }

            /** @var $ParentCategory Category */
            $ParentCategory = $this->categoryRepository->find($row[$headerByKey['parent_category_id']]);
            if (!$ParentCategory) {
                $this->addErrorWithLineNo('親カテゴリIDが存在しません。');
                return false;
            }
        }
        $Category->setParent($ParentCategory);

        // Level
        if (isset($row['階層']) && StringUtil::isNotBlank($row['階層'])) {
            if ($ParentCategory == null && $row['階層'] != 1) {
                $this->addErrorWithLineNo('親カテゴリIDが存在しません。');
                return false;
            }
            $level = StringUtil::trimAll($row['階層']);
        } else {
            $level = 1;
            if ($ParentCategory) {
                $level = $ParentCategory->getHierarchy() + 1;
            }
        }

        $Category->setHierarchy($level);

        if ($this->eccubeConfig['eccube_category_nest_level'] < $Category->getHierarchy()) {
            $this->addErrorWithLineNo('カテゴリが最大レベルを超えているため設定できません。');
            return false;
        }

        $this->entityManager->persist($Category);
        $this->categoryRepository->save($Category);


        // locale用の従属テーブルの更新
        // CategoryのIDが必要なので、Categoryの更新後に処理する。

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            $LocaleCategory = $this->localeCategoryRepository->findOneBy([
                'locale' => $locale,
                'parent_id' => $Category->getId(),
            ]);
            if (!$LocaleCategory) {
                // 新規作成
                $LocaleCategory = new LocaleCategory();
                $LocaleCategory->setParentId($Category->getId());
                $LocaleCategory->setCategory($Category);
                $LocaleCategory->setLocale($locale);
            }

            $name = $headerByKey['category_name_' . $locale];
            if (!isset($row[$name]) || StringUtil::isBlank($row[$name])) {
                $LocaleCategory->setName($Category->getName());
            } else {
                $LocaleCategory->setName($row[$name]);
            }

            $this->entityManager->persist($LocaleCategory);
        }

        $this->entityManager->flush();

        return true;
    }

    public function getCsvHeader(): array
    {
        $locales = $this->eccubeConfig['multi_lingual_locales'];

        $columns = [
            trans('admin.product.category_csv.category_id_col') => [
                'id' => 'id',
                'description' => 'admin.product.category_csv.category_id_description',
                'required' => false,
            ],
            trans('admin.product.category_csv.category_name_col') => [
                'id' => 'category_name',
                'description' => 'admin.product.category_csv.category_name_description',
                'required' => true,
            ],
            trans('admin.product.category_csv.parent_category_id_col') => [
                'id' => 'parent_category_id',
                'description' => 'admin.product.category_csv.parent_category_id_description',
                'required' => false,
            ],
            trans('admin.product.category_csv.delete_flag_col') => [
                'id' => 'category_del_flg',
                'description' => 'admin.product.category_csv.delete_flag_description',
                'required' => false,
            ],
        ];

        foreach ($locales as $locale) {
            $columns[trans('admin.product.category_csv.category_name_col') . "({$locale})"] = [
                'id' => 'category_name_' . $locale,
                'description' => 'admin.product.category_csv.category_name_description',
                'required' => false,
            ];
        }

        return $columns;
    }
}