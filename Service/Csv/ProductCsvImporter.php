<?php
/** @noinspection DuplicatedCode */
/** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual\Service\Csv;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\Constant;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Category;
use Eccube\Entity\ClassCategory;
use Eccube\Entity\DeliveryDuration;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Master\SaleType;
use Eccube\Entity\Product;
use Eccube\Entity\ProductCategory;
use Eccube\Entity\ProductClass;
use Eccube\Entity\ProductImage;
use Eccube\Entity\ProductStock;
use Eccube\Entity\ProductTag;
use Eccube\Entity\Tag;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\ClassCategoryRepository;
use Eccube\Repository\DeliveryDurationRepository;
use Eccube\Repository\Master\ProductStatusRepository;
use Eccube\Repository\Master\SaleTypeRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\TagRepository;
use Eccube\Repository\TaxRuleRepository;
use Eccube\Service\CsvImportService;
use Eccube\Util\CacheUtil;
use Eccube\Util\StringUtil;
use Plugin\MultiLingual\Entity\LocaleProduct;
use Plugin\MultiLingual\Repository\LocaleProductRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * Eccube/Controller/Admin/Product/CsvImportController.php
 * のインポート処理を抜き出し多言語対応の処理を追加したもの。
 * 4.0.6-p1ベース
 */
class ProductCsvImporter extends AbstractCsvImporter
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
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var LocaleProductRepository
     */
    private $localeProductRepository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var ClassCategoryRepository
     */
    private $classCategoryRepository;

    /**
     * @var ProductStatusRepository
     */
    private $productStatusRepository;

    /**
     * @var TaxRuleRepository
     */
    private $taxRuleRepository;

    /**
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * @var SaleTypeRepository
     */
    private $saleTypeRepository;

    /**
     * @var DeliveryDurationRepository
     */
    private $deliveryDurationRepository;

    /**
     * @var BaseInfo
     */
    private $BaseInfo;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var CacheUtil
     */
    private $cacheUtil;

    /**
     * @var array<string, string>
     */
    private $headerByKey = [];

    /**
     * PaymentRegisterExtension constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param EccubeConfig $eccubeConfig
     * @param ProductRepository $productRepository
     * @param LocaleProductRepository $localeProductRepository
     * @param CategoryRepository $categoryRepository
     * @param ClassCategoryRepository $classCategoryRepository
     * @param ProductStatusRepository $productStatusRepository
     * @param TaxRuleRepository $taxRuleRepository
     * @param TagRepository $tagRepository
     * @param SaleTypeRepository $saleTypeRepository
     * @param DeliveryDurationRepository $deliveryDurationRepository
     * @param BaseInfoRepository $baseInfoRepository
     * @param ValidatorInterface $validator
     * @param CacheUtil $cacheUtil
     * @throws \Exception
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        EccubeConfig $eccubeConfig,
        ProductRepository $productRepository,
        LocaleProductRepository $localeProductRepository,
        CategoryRepository $categoryRepository,
        ClassCategoryRepository $classCategoryRepository,
        ProductStatusRepository $productStatusRepository,
        TaxRuleRepository $taxRuleRepository,
        TagRepository $tagRepository,
        SaleTypeRepository $saleTypeRepository,
        DeliveryDurationRepository $deliveryDurationRepository,
        BaseInfoRepository $baseInfoRepository,
        ValidatorInterface $validator,
        CacheUtil $cacheUtil
    )
    {
        $this->entityManager = $entityManager;
        $this->eccubeConfig = $eccubeConfig;
        $this->productRepository = $productRepository;
        $this->localeProductRepository = $localeProductRepository;
        $this->categoryRepository = $categoryRepository;
        $this->classCategoryRepository = $classCategoryRepository;
        $this->productStatusRepository = $productStatusRepository;
        $this->taxRuleRepository = $taxRuleRepository;
        $this->tagRepository = $tagRepository;
        $this->saleTypeRepository = $saleTypeRepository;
        $this->deliveryDurationRepository = $deliveryDurationRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->validator = $validator;
        $this->cacheUtil = $cacheUtil;
    }

    /**
     * {@inheritdoc}
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

        $headerSize = count($columnHeaders);
        $this->headerByKey = array_flip(array_map($getId, $headers));
        $deleteImages = [];

        $this->entityManager->getConfiguration()->setSQLLogger(null);
        $this->entityManager->getConnection()->beginTransaction();
        try {
            // CSVファイルの登録処理
            $this->lineNo = 1;
            foreach ($importService as $row) {
                if ($headerSize != count($row)) {
                    $message = trans('admin.common.csv_invalid_format_line', ['%line%' => $this->lineNo]);
                    $this->addError($message);
                    return false;
                }

                if (!$this->importRow($importService, $row, $deleteImages)) {
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

        // 画像ファイルの削除(commit後に削除させる)
        foreach ($deleteImages as $images) {
            foreach ($images as $image) {
                try {
                    $fs = new Filesystem();
                    $fs->remove($this->eccubeConfig['eccube_save_image_dir'].'/'.$image);
                } catch (\Exception $e) {
                    // エラーが発生しても無視する
                }
            }
        }

        return true;
    }

    /**
     * 行のインポート処理。
     * メソッド内からlineNo, headerByKeyを参照するので設定しておくこと。
     *
     * @param CsvImportService $importService
     * @param array $row
     * @param array $deleteImages
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     */
    private function importRow(CsvImportService $importService, array $row, array &$deleteImages): bool
    {
        $headerByKey = $this->headerByKey;

        $line = $this->lineNo;

        if (!isset($row[$headerByKey['id']]) || StringUtil::isBlank($row[$headerByKey['id']])) {
            $Product = new Product();
            $this->entityManager->persist($Product);
        } else {
            if (preg_match('/^\d+$/', $row[$headerByKey['id']])) {
                /** @var Product $Product */
                $Product = $this->productRepository->find($row[$headerByKey['id']]);
                if (!$Product) {
                    $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['id']]);
                    $this->addError($message);
                    return false;
                }
            } else {
                $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['id']]);
                $this->addError($message);
                return false;
            }

            if (isset($row[$headerByKey['product_del_flg']])) {
                if (StringUtil::isNotBlank($row[$headerByKey['product_del_flg']]) && $row[$headerByKey['product_del_flg']] == (string) Constant::ENABLED) {
                    // 商品を物理削除
                    $deleteImages[] = $Product->getProductImage();

                    try {
                        $this->productRepository->delete($Product);
                        $this->entityManager->flush();

                        return true;
                    } catch (ForeignKeyConstraintViolationException $e) {
                        $message = trans('admin.common.csv_invalid_foreign_key', ['%line%' => $line, '%name%' => $Product->getName()]);
                        $this->addError($message);
                        return false;
                    }
                }
            }
        }

        if (StringUtil::isBlank($row[$headerByKey['status']])) {
            $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['status']]);
            $this->addError($message);
        } else {
            if (preg_match('/^\d+$/', $row[$headerByKey['status']])) {
                /** @var ProductStatus $ProductStatus */
                $ProductStatus = $this->productStatusRepository->find($row[$headerByKey['status']]);
                if (!$ProductStatus) {
                    $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['status']]);
                    $this->addError($message);
                } else {
                    $Product->setStatus($ProductStatus);
                }
            } else {
                $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['status']]);
                $this->addError($message);
            }
        }

        if (StringUtil::isBlank($row[$headerByKey['name']])) {
            $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['name']]);
            $this->addError($message);
            return false;
        } else {
            $Product->setName(StringUtil::trimAll($row[$headerByKey['name']]));
        }

        if (isset($row[$headerByKey['note']])) {
            if (StringUtil::isNotBlank($row[$headerByKey['note']])) {
                $Product->setNote(StringUtil::trimAll($row[$headerByKey['note']]));
            } else {
                $Product->setNote(null);
            }
        }

        if (isset($row[$headerByKey['description_list']])) {
            if (StringUtil::isNotBlank($row[$headerByKey['description_list']])) {
                $Product->setDescriptionList(StringUtil::trimAll($row[$headerByKey['description_list']]));
            } else {
                $Product->setDescriptionList(null);
            }
        }

        if (isset($row[$headerByKey['description_detail']])) {
            if (StringUtil::isNotBlank($row[$headerByKey['description_detail']])) {
                if (mb_strlen($row[$headerByKey['description_detail']]) > $this->eccubeConfig['eccube_ltext_len']) {
                    $message = trans('admin.common.csv_invalid_description_detail_upper_limit', [
                        '%line%' => $line,
                        '%name%' => $headerByKey['description_detail'],
                        '%max%' => $this->eccubeConfig['eccube_ltext_len'],
                    ]);
                    $this->addError($message);
                    return false;
                } else {
                    $Product->setDescriptionDetail(StringUtil::trimAll($row[$headerByKey['description_detail']]));
                }
            } else {
                $Product->setDescriptionDetail(null);
            }
        }

        if (isset($row[$headerByKey['search_word']])) {
            if (StringUtil::isNotBlank($row[$headerByKey['search_word']])) {
                $Product->setSearchWord(StringUtil::trimAll($row[$headerByKey['search_word']]));
            } else {
                $Product->setSearchWord(null);
            }
        }

        if (isset($row[$headerByKey['free_area']])) {
            if (StringUtil::isNotBlank($row[$headerByKey['free_area']])) {
                $Product->setFreeArea(StringUtil::trimAll($row[$headerByKey['free_area']]));
            } else {
                $Product->setFreeArea(null);
            }
        }

        // 商品画像登録
        $this->createProductImage($row, $Product, $importService, $headerByKey);

        $this->entityManager->flush();

        // 商品カテゴリ登録
        $this->createProductCategory($row, $Product, $importService, $headerByKey);

        //タグ登録
        $this->createProductTag($row, $Product, $importService, $headerByKey);

        // 商品規格が存在しなければ新規登録
        /** @var Collection $ProductClasses */
        $ProductClasses = $Product->getProductClasses();
        if ($ProductClasses->count() < 1) {
            // 規格分類1(ID)がセットされていると規格なし商品、規格あり商品を作成
            $ProductClassOrg = $this->createProductClass($row, $Product, $importService, $headerByKey);
            if ($this->BaseInfo->isOptionProductDeliveryFee()) {
                if (isset($row[$headerByKey['delivery_fee']]) && StringUtil::isNotBlank($row[$headerByKey['delivery_fee']])) {
                    $deliveryFee = str_replace(',', '', $row[$headerByKey['delivery_fee']]);
                    $errors = $this->validator->validate($deliveryFee, new GreaterThanOrEqual(['value' => 0]));
                    if ($errors->count() === 0) {
                        $ProductClassOrg->setDeliveryFee($deliveryFee);
                    } else {
                        $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['delivery_fee']]);
                        $this->addError($message);
                    }
                }
            }

            // 商品別税率機能が有効の場合に税率を更新
            if ($this->BaseInfo->isOptionProductTaxRule()) {
                if (isset($row[$headerByKey['tax_rate']]) && StringUtil::isNotBlank($row[$headerByKey['tax_rate']])) {
                    $taxRate = $row[$headerByKey['tax_rate']];
                    $errors = $this->validator->validate($taxRate, new GreaterThanOrEqual(['value' => 0]));
                    if ($errors->count() === 0) {
                        if ($ProductClassOrg->getTaxRule()) {
                            // 商品別税率の設定があれば税率を更新
                            $ProductClassOrg->getTaxRule()->setTaxRate($taxRate);
                        } else {
                            // 商品別税率の設定がなければ新規作成
                            $TaxRule = $this->taxRuleRepository->newTaxRule();
                            $TaxRule->setTaxRate($taxRate);
                            $TaxRule->setApplyDate(new \DateTime());
                            $TaxRule->setProduct($Product);
                            $TaxRule->setProductClass($ProductClassOrg);
                            $ProductClassOrg->setTaxRule($TaxRule);
                        }
                    } else {
                        $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['tax_rate']]);
                        $this->addError($message);
                    }
                } else {
                    // 税率の入力がなければ税率の設定を削除
                    if ($ProductClassOrg->getTaxRule()) {
                        $this->taxRuleRepository->delete($ProductClassOrg->getTaxRule());
                        $ProductClassOrg->setTaxRule(null);
                    }
                }
            }

            if (isset($row[$headerByKey['class_category1']]) && StringUtil::isNotBlank($row[$headerByKey['class_category1']])) {
                if (isset($row[$headerByKey['class_category2']]) && $row[$headerByKey['class_category1']] == $row[$headerByKey['class_category2']]) {
                    $message = trans('admin.common.csv_invalid_not_same', [
                        '%line%' => $line,
                        '%name1%' => $headerByKey['class_category1'],
                        '%name2%' => $headerByKey['class_category2'],
                    ]);
                    $this->addError($message);
                } else {
                    // 商品規格あり
                    // 規格分類あり商品を作成
                    $ProductClass = clone $ProductClassOrg;
                    $ProductStock = clone $ProductClassOrg->getProductStock();

                    // 規格分類1、規格分類2がnullであるデータを非表示
                    $ProductClassOrg->setVisible(false);

                    // 規格分類1、2をそれぞれセットし作成
                    $ClassCategory1 = null;
                    if (preg_match('/^\d+$/', $row[$headerByKey['class_category1']])) {
                        /** @var ?ClassCategory $ClassCategory1 */
                        $ClassCategory1 = $this->classCategoryRepository->find($row[$headerByKey['class_category1']]);
                        if (!$ClassCategory1) {
                            $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category1']]);
                            $this->addError($message);
                        } else {
                            $ProductClass->setClassCategory1($ClassCategory1);
                        }
                    } else {
                        $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category1']]);
                        $this->addError($message);
                    }

                    if (isset($row[$headerByKey['class_category2']]) && StringUtil::isNotBlank($row[$headerByKey['class_category2']])) {
                        if (preg_match('/^\d+$/', $row[$headerByKey['class_category2']])) {
                            /** @var ?ClassCategory $ClassCategory2 */
                            $ClassCategory2 = $this->classCategoryRepository->find($row[$headerByKey['class_category2']]);
                            if (!$ClassCategory2) {
                                $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category2']]);
                                $this->addError($message);
                            } else {
                                if ($ClassCategory1 &&
                                    ($ClassCategory1->getClassName()->getId() == $ClassCategory2->getClassName()->getId())
                                ) {
                                    $message = trans('admin.common.csv_invalid_not_same', ['%line%' => $line, '%name1%' => $headerByKey['class_category1'], '%name2%' => $headerByKey['class_category2']]);
                                    $this->addError($message);
                                } else {
                                    $ProductClass->setClassCategory2($ClassCategory2);
                                }
                            }
                        } else {
                            $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category2']]);
                            $this->addError($message);
                        }
                    }
                    $ProductClass->setProductStock($ProductStock);
                    $ProductStock->setProductClass($ProductClass);

                    $this->entityManager->persist($ProductClass);
                    $this->entityManager->persist($ProductStock);
                }
            } else {
                if (isset($row[$headerByKey['class_category2']]) && StringUtil::isNotBlank($row[$headerByKey['class_category2']])) {
                    $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category2']]);
                    $this->addError($message);
                }
            }
        } else {
            // 商品規格の更新
            $flag = false;
            $classCategoryId1 = StringUtil::isBlank($row[$headerByKey['class_category1']]) ? null : $row[$headerByKey['class_category1']];
            $classCategoryId2 = StringUtil::isBlank($row[$headerByKey['class_category2']]) ? null : $row[$headerByKey['class_category2']];

            foreach ($ProductClasses as $pc) {
                $classCategory1 = is_null($pc->getClassCategory1()) ? null : $pc->getClassCategory1()->getId();
                $classCategory2 = is_null($pc->getClassCategory2()) ? null : $pc->getClassCategory2()->getId();

                // 登録されている商品規格を更新
                if ($classCategory1 == $classCategoryId1 &&
                    $classCategory2 == $classCategoryId2
                ) {
                    $this->updateProductClass($row, $Product, $pc, $importService, $headerByKey);

                    if ($this->BaseInfo->isOptionProductDeliveryFee()) {
                        if (isset($row[$headerByKey['delivery_fee']]) && StringUtil::isNotBlank($row[$headerByKey['delivery_fee']])) {
                            $deliveryFee = str_replace(',', '', $row[$headerByKey['delivery_fee']]);
                            $errors = $this->validator->validate($deliveryFee, new GreaterThanOrEqual(['value' => 0]));
                            if ($errors->count() === 0) {
                                $pc->setDeliveryFee($deliveryFee);
                            } else {
                                $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['delivery_fee']]);
                                $this->addError($message);
                            }
                        }
                    }

                    // 商品別税率機能が有効の場合に税率を更新
                    if ($this->BaseInfo->isOptionProductTaxRule()) {
                        if (isset($row[$headerByKey['tax_rate']]) && StringUtil::isNotBlank($row[$headerByKey['tax_rate']])) {
                            $taxRate = $row[$headerByKey['tax_rate']];
                            $errors = $this->validator->validate($taxRate, new GreaterThanOrEqual(['value' => 0]));
                            if ($errors->count() === 0) {
                                if ($pc->getTaxRule()) {
                                    // 商品別税率の設定があれば税率を更新
                                    $pc->getTaxRule()->setTaxRate($taxRate);
                                } else {
                                    // 商品別税率の設定がなければ新規作成
                                    $TaxRule = $this->taxRuleRepository->newTaxRule();
                                    $TaxRule->setTaxRate($taxRate);
                                    $TaxRule->setApplyDate(new \DateTime());
                                    $TaxRule->setProduct($Product);
                                    $TaxRule->setProductClass($pc);
                                    $pc->setTaxRule($TaxRule);
                                }
                            } else {
                                $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['tax_rate']]);
                                $this->addError($message);
                            }
                        } else {
                            // 税率の入力がなければ税率の設定を削除
                            if ($pc->getTaxRule()) {
                                $this->taxRuleRepository->delete($pc->getTaxRule());
                                $pc->setTaxRule(null);
                            }
                        }
                    }

                    $flag = true;
                    break;
                }
            }

            // 商品規格を登録
            if (!$flag) {
                $pc = $ProductClasses[0];
                if ($pc->getClassCategory1() == null &&
                    $pc->getClassCategory2() == null
                ) {
                    // 規格分類1、規格分類2がnullであるデータを非表示
                    $pc->setVisible(false);
                }

                if (isset($row[$headerByKey['class_category1']]) && isset($row[$headerByKey['class_category2']])
                    && $row[$headerByKey['class_category1']] == $row[$headerByKey['class_category2']]) {
                    $message = trans('admin.common.csv_invalid_not_same', [
                        '%line%' => $line,
                        '%name1%' => $headerByKey['class_category1'],
                        '%name2%' => $headerByKey['class_category2'],
                    ]);
                    $this->addError($message);
                } else {
                    // 必ず規格分類1がセットされている
                    // 規格分類1、2をそれぞれセットし作成
                    $ClassCategory1 = null;
                    if (preg_match('/^\d+$/', $classCategoryId1)) {
                        $ClassCategory1 = $this->classCategoryRepository->find($classCategoryId1);
                        if (!$ClassCategory1) {
                            $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category1']]);
                            $this->addError($message);
                        }
                    } else {
                        $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category1']]);
                        $this->addError($message);
                    }

                    $ClassCategory2 = null;
                    if (isset($row[$headerByKey['class_category2']]) && StringUtil::isNotBlank($row[$headerByKey['class_category2']])) {
                        if ($pc->getClassCategory1() != null && $pc->getClassCategory2() == null) {
                            $message = trans('admin.common.csv_invalid_can_not', ['%line%' => $line, '%name%' => $headerByKey['class_category2']]);
                            $this->addError($message);
                        } else {
                            if (preg_match('/^\d+$/', $classCategoryId2)) {
                                $ClassCategory2 = $this->classCategoryRepository->find($classCategoryId2);
                                if (!$ClassCategory2) {
                                    $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category2']]);
                                    $this->addError($message);
                                } else {
                                    if ($ClassCategory1 &&
                                        ($ClassCategory1->getClassName()->getId() == $ClassCategory2->getClassName()->getId())
                                    ) {
                                        $message = trans('admin.common.csv_invalid_not_same', [
                                            '%line%' => $line,
                                            '%name1%' => $headerByKey['class_category1'],
                                            '%name2%' => $headerByKey['class_category2'],
                                        ]);
                                        $this->addError($message);
                                    }
                                }
                            } else {
                                $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category2']]);
                                $this->addError($message);
                            }
                        }
                    } else {
                        if ($pc->getClassCategory1() != null && $pc->getClassCategory2() != null) {
                            $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['class_category2']]);
                            $this->addError($message);
                        }
                    }
                    $ProductClass = $this->createProductClass($row, $Product, $importService, $headerByKey, $ClassCategory1, $ClassCategory2);

                    if ($this->BaseInfo->isOptionProductDeliveryFee()) {
                        if (isset($row[$headerByKey['delivery_fee']]) && StringUtil::isNotBlank($row[$headerByKey['delivery_fee']])) {
                            $deliveryFee = str_replace(',', '', $row[$headerByKey['delivery_fee']]);
                            $errors = $this->validator->validate($deliveryFee, new GreaterThanOrEqual(['value' => 0]));
                            if ($errors->count() === 0) {
                                $ProductClass->setDeliveryFee($deliveryFee);
                            } else {
                                $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['delivery_fee']]);
                                $this->addError($message);
                            }
                        }
                    }

                    // 商品別税率機能が有効の場合に税率を更新
                    if ($this->BaseInfo->isOptionProductTaxRule()) {
                        if (isset($row[$headerByKey['tax_rate']]) && StringUtil::isNotBlank($row[$headerByKey['tax_rate']])) {
                            $taxRate = $row[$headerByKey['tax_rate']];
                            $errors = $this->validator->validate($taxRate, new GreaterThanOrEqual(['value' => 0]));
                            if ($errors->count() === 0) {
                                $TaxRule = $this->taxRuleRepository->newTaxRule();
                                $TaxRule->setTaxRate($taxRate);
                                $TaxRule->setApplyDate(new \DateTime());
                                $TaxRule->setProduct($Product);
                                $TaxRule->setProductClass($ProductClass);
                                $ProductClass->setTaxRule($TaxRule);
                            } else {
                                $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['tax_rate']]);
                                $this->addError($message);
                            }
                        }
                    }

                    $Product->addProductClass($ProductClass);
                }
            }
        }
        if ($this->hasErrors()) {
            return false;
        }

        $this->entityManager->persist($Product);

        $this->entityManager->flush();

        // locale用の従属テーブルの更新
        // ProductのIDが必要なので、Productの更新後に処理する。

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            $LocaleProduct = $this->localeProductRepository->findOneBy([
                'locale' => $locale,
                'parent_id' => $Product->getId(),
            ]);
            if (!$LocaleProduct) {
                // 新規作成
                $LocaleProduct = new LocaleProduct();
                $LocaleProduct->setParentId($Product->getId());
                $LocaleProduct->setProduct($Product);
                $LocaleProduct->setLocale($locale);
            }

            $name = $headerByKey['name_' . $locale];
            if (!isset($row[$name]) || StringUtil::isBlank($row[$name])) {
                $LocaleProduct->setName($Product->getName());
            } else {
                $LocaleProduct->setName($row[$name]);
            }

            $name = $headerByKey['description_list_' . $locale];
            if (!isset($row[$name]) || StringUtil::isBlank($row[$name])) {
                $LocaleProduct->setDescriptionList($Product->getDescriptionList());
            } else {
                $LocaleProduct->setDescriptionList($row[$name]);
            }

            $name = $headerByKey['description_detail_' . $locale];
            if (!isset($row[$name]) || StringUtil::isBlank($row[$name])) {
                $LocaleProduct->setDescriptionDetail($Product->getDescriptionDetail());
            } else {
                $LocaleProduct->setDescriptionDetail($row[$name]);
            }

            $name = $headerByKey['free_area_' . $locale];
            if (!isset($row[$name]) || StringUtil::isBlank($row[$name])) {
                $LocaleProduct->setFreeArea($Product->getFreeArea());
            } else {
                $LocaleProduct->setFreeArea($row[$name]);
            }

            $this->entityManager->persist($LocaleProduct);
        }

        $this->entityManager->flush();

        return true;
    }

    /**
     * 商品画像の削除、登録
     *
     * @param $row
     * @param Product $Product
     * @param CsvImportService $data
     * @param $headerByKey
     */
    private function createProductImage($row, Product $Product, CsvImportService $data, $headerByKey)
    {
        if (!isset($row[$headerByKey['product_image']])) {
            return;
        }
        if (StringUtil::isNotBlank($row[$headerByKey['product_image']])) {
            // 画像の削除
            $ProductImages = $Product->getProductImage();
            foreach ($ProductImages as $ProductImage) {
                $Product->removeProductImage($ProductImage);
                $this->entityManager->remove($ProductImage);
            }

            // 画像の登録
            $images = explode(',', $row[$headerByKey['product_image']]);

            $sortNo = 1;

            $pattern = "/\\$|^.*.\.\\\.*|\/$|^.*.\.\/\.*/";
            foreach ($images as $image) {
                $fileName = StringUtil::trimAll($image);

                // 商品画像名のフォーマットチェック
                if (strlen($fileName) > 0 && preg_match($pattern, $fileName)) {
                    $message = trans('admin.common.csv_invalid_image', ['%line%' => $data->key() + 1, '%name%' => $headerByKey['product_image']]);
                    $this->addError($message);
                } else {
                    // 空文字は登録対象外
                    if (!empty($fileName)) {
                        $ProductImage = new ProductImage();
                        $ProductImage->setFileName($fileName);
                        $ProductImage->setProduct($Product);
                        $ProductImage->setSortNo($sortNo);

                        $Product->addProductImage($ProductImage);
                        $sortNo++;
                        $this->entityManager->persist($ProductImage);
                    }
                }
            }
        }
    }

    /**
     * 商品カテゴリの削除、登録
     *
     * @param $row
     * @param Product $Product
     * @param CsvImportService $data
     * @param $headerByKey
     */
    private function createProductCategory($row, Product $Product, CsvImportService $data, $headerByKey)
    {
        if (!isset($row[$headerByKey['product_category']])) {
            return;
        }
        // カテゴリの削除
        $ProductCategories = $Product->getProductCategories();
        foreach ($ProductCategories as $ProductCategory) {
            $Product->removeProductCategory($ProductCategory);
            $this->entityManager->remove($ProductCategory);
            $this->entityManager->flush();
        }

        if (StringUtil::isNotBlank($row[$headerByKey['product_category']])) {
            // カテゴリの登録
            $categories = explode(',', $row[$headerByKey['product_category']]);
            $sortNo = 1;
            $categoriesIdList = [];
            foreach ($categories as $category) {
                $line = $data->key() + 1;
                if (preg_match('/^\d+$/', $category)) {
                    /** @var ?Category $Category */
                    $Category = $this->categoryRepository->find($category);
                    if (!$Category) {
                        $message = trans('admin.common.csv_invalid_not_found_target', [
                            '%line%' => $line,
                            '%name%' => $headerByKey['product_category'],
                            '%target_name%' => $category,
                        ]);
                        $this->addError($message);
                    } else {
                        foreach ($Category->getPath() as $ParentCategory) {
                            if (!isset($categoriesIdList[$ParentCategory->getId()])) {
                                $ProductCategory = $this->makeProductCategory($Product, $ParentCategory, $sortNo);
                                $this->entityManager->persist($ProductCategory);
                                $sortNo++;

                                $Product->addProductCategory($ProductCategory);
                                $categoriesIdList[$ParentCategory->getId()] = true;
                            }
                        }
                        if (!isset($categoriesIdList[$Category->getId()])) {
                            $ProductCategory = $this->makeProductCategory($Product, $Category, $sortNo);
                            $sortNo++;
                            $this->entityManager->persist($ProductCategory);
                            $Product->addProductCategory($ProductCategory);
                            $categoriesIdList[$Category->getId()] = true;
                        }
                    }
                } else {
                    $message = trans('admin.common.csv_invalid_not_found_target', [
                        '%line%' => $line,
                        '%name%' => $headerByKey['product_category'],
                        '%target_name%' => $category,
                    ]);
                    $this->addError($message);
                }
            }
        }
    }

    /**
     * タグの登録
     *
     * @param array $row
     * @param Product $Product
     * @param CsvImportService $data
     * @param $headerByKey
     */
    private function createProductTag(array $row, Product $Product, CsvImportService $data, $headerByKey)
    {
        if (!isset($row[$headerByKey['product_tag']])) {
            return;
        }
        // タグの削除
        $ProductTags = $Product->getProductTag();
        foreach ($ProductTags as $ProductTag) {
            $Product->removeProductTag($ProductTag);
            $this->entityManager->remove($ProductTag);
        }

        if (StringUtil::isNotBlank($row[$headerByKey['product_tag']])) {
            // タグの登録
            $tags = explode(',', $row[$headerByKey['product_tag']]);
            foreach ($tags as $tag_id) {
                $Tag = null;
                if (preg_match('/^\d+$/', $tag_id)) {
                    /** @var ?Tag $Tag */
                    $Tag = $this->tagRepository->find($tag_id);

                    if ($Tag) {
                        $ProductTags = new ProductTag();
                        $ProductTags
                            ->setProduct($Product)
                            ->setTag($Tag);

                        $Product->addProductTag($ProductTags);

                        $this->entityManager->persist($ProductTags);
                    }
                }
                if (!$Tag) {
                    $message = trans('admin.common.csv_invalid_not_found_target', [
                        '%line%' => $data->key() + 1,
                        '%name%' => $headerByKey['product_tag'],
                        '%target_name%' => $tag_id,
                    ]);
                    $this->addError($message);
                }
            }
        }
    }

    /**
     * 商品規格分類1、商品規格分類2がnullとなる商品規格情報を作成
     *
     * @param $row
     * @param Product $Product
     * @param CsvImportService $data
     * @param $headerByKey
     * @param null $ClassCategory1
     * @param null $ClassCategory2
     *
     * @return ProductClass
     */
    private function createProductClass($row, Product $Product, CsvImportService $data, $headerByKey, $ClassCategory1 = null, $ClassCategory2 = null): ProductClass
    {
        // 規格分類1、規格分類2がnullとなる商品を作成
        $ProductClass = new ProductClass();
        $ProductClass->setProduct($Product);
        $ProductClass->setVisible(true);

        $line = $data->key() + 1;
        if (isset($row[$headerByKey['sale_type']]) && StringUtil::isNotBlank($row[$headerByKey['sale_type']])) {
            if (preg_match('/^\d+$/', $row[$headerByKey['sale_type']])) {
                /** @var ?SaleType $SaleType */
                $SaleType = $this->saleTypeRepository->find($row[$headerByKey['sale_type']]);
                if (!$SaleType) {
                    $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['sale_type']]);
                    $this->addError($message);
                } else {
                    $ProductClass->setSaleType($SaleType);
                }
            } else {
                $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['sale_type']]);
                $this->addError($message);
            }
        } else {
            $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['sale_type']]);
            $this->addError($message);
        }

        $ProductClass->setClassCategory1($ClassCategory1);
        $ProductClass->setClassCategory2($ClassCategory2);

        if (isset($row[$headerByKey['delivery_date']]) && StringUtil::isNotBlank($row[$headerByKey['delivery_date']])) {
            if (preg_match('/^\d+$/', $row[$headerByKey['delivery_date']])) {
                /** @var ?DeliveryDuration $DeliveryDuration */
                $DeliveryDuration = $this->deliveryDurationRepository->find($row[$headerByKey['delivery_date']]);
                if (!$DeliveryDuration) {
                    $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['delivery_date']]);
                    $this->addError($message);
                } else {
                    $ProductClass->setDeliveryDuration($DeliveryDuration);
                }
            } else {
                $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['delivery_date']]);
                $this->addError($message);
            }
        }

        if (isset($row[$headerByKey['product_code']]) && StringUtil::isNotBlank($row[$headerByKey['product_code']])) {
            $ProductClass->setCode(StringUtil::trimAll($row[$headerByKey['product_code']]));
        } else {
            $ProductClass->setCode(null);
        }

        if (!isset($row[$headerByKey['stock_unlimited']])
            || StringUtil::isBlank($row[$headerByKey['stock_unlimited']])
            || $row[$headerByKey['stock_unlimited']] == (string) Constant::DISABLED
        ) {
            $ProductClass->setStockUnlimited(false);
            // 在庫数が設定されていなければエラー
            if (isset($row[$headerByKey['stock']]) && StringUtil::isNotBlank($row[$headerByKey['stock']])) {
                $stock = str_replace(',', '', $row[$headerByKey['stock']]);
                if (preg_match('/^\d+$/', $stock) && $stock >= 0) {
                    $ProductClass->setStock($stock);
                } else {
                    $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['stock']]);
                    $this->addError($message);
                }
            } else {
                $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['stock']]);
                $this->addError($message);
            }
        } elseif ($row[$headerByKey['stock_unlimited']] == (string) Constant::ENABLED) {
            $ProductClass->setStockUnlimited(true);
            $ProductClass->setStock(null);
        } else {
            $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['stock_unlimited']]);
            $this->addError($message);
        }

        if (isset($row[$headerByKey['sale_limit']]) && StringUtil::isNotBlank($row[$headerByKey['sale_limit']])) {
            $saleLimit = str_replace(',', '', $row[$headerByKey['sale_limit']]);
            if (preg_match('/^\d+$/', $saleLimit) && $saleLimit >= 0) {
                $ProductClass->setSaleLimit($saleLimit);
            } else {
                $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['sale_limit']]);
                $this->addError($message);
            }
        }

        if (isset($row[$headerByKey['price01']]) && StringUtil::isNotBlank($row[$headerByKey['price01']])) {
            $price01 = str_replace(',', '', $row[$headerByKey['price01']]);
            $errors = $this->validator->validate($price01, new GreaterThanOrEqual(['value' => 0]));
            if ($errors->count() === 0) {
                $ProductClass->setPrice01($price01);
            } else {
                $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['price01']]);
                $this->addError($message);
            }
        }

        if (isset($row[$headerByKey['price02']]) && StringUtil::isNotBlank($row[$headerByKey['price02']])) {
            $price02 = str_replace(',', '', $row[$headerByKey['price02']]);
            $errors = $this->validator->validate($price02, new GreaterThanOrEqual(['value' => 0]));
            if ($errors->count() === 0) {
                $ProductClass->setPrice02($price02);
            } else {
                $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['price02']]);
                $this->addError($message);
            }
        } else {
            $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['price02']]);
            $this->addError($message);
        }

        if ($this->BaseInfo->isOptionProductDeliveryFee()) {
            if (isset($row[$headerByKey['delivery_fee']]) && StringUtil::isNotBlank($row[$headerByKey['delivery_fee']])) {
                $delivery_fee = str_replace(',', '', $row[$headerByKey['delivery_fee']]);
                $errors = $this->validator->validate($delivery_fee, new GreaterThanOrEqual(['value' => 0]));
                if ($errors->count() === 0) {
                    $ProductClass->setDeliveryFee($delivery_fee);
                } else {
                    $message = trans('admin.common.csv_invalid_greater_than_zero',
                        ['%line%' => $line, '%name%' => $headerByKey['delivery_fee']]);
                    $this->addError($message);
                }
            }
        }

        $Product->addProductClass($ProductClass);
        $ProductStock = new ProductStock();
        $ProductClass->setProductStock($ProductStock);
        $ProductStock->setProductClass($ProductClass);

        if (!$ProductClass->isStockUnlimited()) {
            $ProductStock->setStock($ProductClass->getStock());
        } else {
            // 在庫無制限時はnullを設定
            $ProductStock->setStock(null);
        }

        $this->entityManager->persist($ProductClass);
        $this->entityManager->persist($ProductStock);

        return $ProductClass;
    }

    /**
     * 商品規格情報を更新
     *
     * @param $row
     * @param Product $Product
     * @param ProductClass $ProductClass
     * @param CsvImportService $data
     * @param $headerByKey
     * @return ProductClass
     */
    private function updateProductClass($row, Product $Product, ProductClass $ProductClass, CsvImportService $data, $headerByKey): ProductClass
    {
        $ProductClass->setProduct($Product);

        $line = $data->key() + 1;
        if ($row[$headerByKey['sale_type']] == '') {
            $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['sale_type']]);
            $this->addError($message);
        } else {
            if (preg_match('/^\d+$/', $row[$headerByKey['sale_type']])) {
                /** @var ?SaleType $SaleType */
                $SaleType = $this->saleTypeRepository->find($row[$headerByKey['sale_type']]);
                if (!$SaleType) {
                    $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['sale_type']]);
                    $this->addError($message);
                } else {
                    $ProductClass->setSaleType($SaleType);
                }
            } else {
                $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['sale_type']]);
                $this->addError($message);
            }
        }

        // 規格分類1、2をそれぞれセットし作成
        if ($row[$headerByKey['class_category1']] != '') {
            if (preg_match('/^\d+$/', $row[$headerByKey['class_category1']])) {
                /** @var ?ClassCategory $ClassCategory */
                $ClassCategory = $this->classCategoryRepository->find($row[$headerByKey['class_category1']]);
                if (!$ClassCategory) {
                    $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category1']]);
                    $this->addError($message);
                } else {
                    $ProductClass->setClassCategory1($ClassCategory);
                }
            } else {
                $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category1']]);
                $this->addError($message);
            }
        }

        if ($row[$headerByKey['class_category2']] != '') {
            if (preg_match('/^\d+$/', $row[$headerByKey['class_category2']])) {
                /** @var ClassCategory $ClassCategory */
                $ClassCategory = $this->classCategoryRepository->find($row[$headerByKey['class_category2']]);
                if (!$ClassCategory) {
                    $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category2']]);
                    $this->addError($message);
                } else {
                    $ProductClass->setClassCategory2($ClassCategory);
                }
            } else {
                $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['class_category2']]);
                $this->addError($message);
            }
        }

        if ($row[$headerByKey['delivery_date']] != '') {
            if (preg_match('/^\d+$/', $row[$headerByKey['delivery_date']])) {
                /** @var ?DeliveryDuration $DeliveryDuration */
                $DeliveryDuration = $this->deliveryDurationRepository->find($row[$headerByKey['delivery_date']]);
                if (!$DeliveryDuration) {
                    $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['delivery_date']]);
                    $this->addError($message);
                } else {
                    $ProductClass->setDeliveryDuration($DeliveryDuration);
                }
            } else {
                $message = trans('admin.common.csv_invalid_not_found', ['%line%' => $line, '%name%' => $headerByKey['delivery_date']]);
                $this->addError($message);
            }
        }

        if (StringUtil::isNotBlank($row[$headerByKey['product_code']])) {
            $ProductClass->setCode(StringUtil::trimAll($row[$headerByKey['product_code']]));
        } else {
            $ProductClass->setCode(null);
        }

        if (!isset($row[$headerByKey['stock_unlimited']])
            || StringUtil::isBlank($row[$headerByKey['stock_unlimited']])
            || $row[$headerByKey['stock_unlimited']] == (string) Constant::DISABLED
        ) {
            $ProductClass->setStockUnlimited(false);
            // 在庫数が設定されていなければエラー
            if ($row[$headerByKey['stock']] == '') {
                $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['stock']]);
                $this->addError($message);
            } else {
                $stock = str_replace(',', '', $row[$headerByKey['stock']]);
                if (preg_match('/^\d+$/', $stock) && $stock >= 0) {
                    $ProductClass->setStock($row[$headerByKey['stock']]);
                } else {
                    $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['stock']]);
                    $this->addError($message);
                }
            }
        } elseif ($row[$headerByKey['stock_unlimited']] == (string) Constant::ENABLED) {
            $ProductClass->setStockUnlimited(true);
            $ProductClass->setStock(null);
        } else {
            $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['stock_unlimited']]);
            $this->addError($message);
        }

        if ($row[$headerByKey['sale_limit']] != '') {
            $saleLimit = str_replace(',', '', $row[$headerByKey['sale_limit']]);
            if (preg_match('/^\d+$/', $saleLimit) && $saleLimit >= 0) {
                $ProductClass->setSaleLimit($saleLimit);
            } else {
                $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['sale_limit']]);
                $this->addError($message);
            }
        }

        if ($row[$headerByKey['price01']] != '') {
            $price01 = str_replace(',', '', $row[$headerByKey['price01']]);
            $errors = $this->validator->validate($price01, new GreaterThanOrEqual(['value' => 0]));
            if ($errors->count() === 0) {
                $ProductClass->setPrice01($price01);
            } else {
                $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['price01']]);
                $this->addError($message);
            }
        }

        if ($row[$headerByKey['price02']] == '') {
            $message = trans('admin.common.csv_invalid_required', ['%line%' => $line, '%name%' => $headerByKey['price02']]);
            $this->addError($message);
        } else {
            $price02 = str_replace(',', '', $row[$headerByKey['price02']]);
            $errors = $this->validator->validate($price02, new GreaterThanOrEqual(['value' => 0]));
            if ($errors->count() === 0) {
                $ProductClass->setPrice02($price02);
            } else {
                $message = trans('admin.common.csv_invalid_greater_than_zero', ['%line%' => $line, '%name%' => $headerByKey['price02']]);
                $this->addError($message);
            }
        }

        $ProductStock = $ProductClass->getProductStock();

        if (!$ProductClass->isStockUnlimited()) {
            $ProductStock->setStock($ProductClass->getStock());
        } else {
            // 在庫無制限時はnullを設定
            $ProductStock->setStock(null);
        }

        return $ProductClass;
    }

    /**
     * ProductCategory作成
     *
     * @param Product $Product
     * @param Category $Category
     * @param int $sortNo
     *
     * @return ProductCategory
     */
    private function makeProductCategory(Product $Product, Category $Category, int $sortNo): ProductCategory
    {
        $ProductCategory = new ProductCategory();
        $ProductCategory->setProduct($Product);
        $ProductCategory->setProductId($Product->getId());
        $ProductCategory->setCategory($Category);
        $ProductCategory->setCategoryId($Category->getId());

        return $ProductCategory;
    }

    /** @noinspection PhpIllegalArrayKeyTypeInspection */
    public function getCsvHeader(): array
    {
        $locales = $this->eccubeConfig['multi_lingual_locales'];

        $columns = [
            trans('admin.product.product_csv.product_id_col') => [
                'id' => 'id',
                'description' => 'admin.product.product_csv.product_id_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.display_status_col') => [
                'id' => 'status',
                'description' => 'admin.product.product_csv.display_status_description',
                'required' => true,
            ],
            trans('admin.product.product_csv.product_name_col') => [
                'id' => 'name',
                'description' => 'admin.product.product_csv.product_name_description',
                'required' => true,
            ],
            trans('admin.product.product_csv.shop_memo_col') => [
                'id' => 'note',
                'description' => 'admin.product.product_csv.shop_memo_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.description_list_col') => [
                'id' => 'description_list',
                'description' => 'admin.product.product_csv.description_list_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.description_detail_col') => [
                'id' => 'description_detail',
                'description' => 'admin.product.product_csv.description_detail_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.keyword_col') => [
                'id' => 'search_word',
                'description' => 'admin.product.product_csv.keyword_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.free_area_col') => [
                'id' => 'free_area',
                'description' => 'admin.product.product_csv.free_area_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.delete_flag_col') => [
                'id' => 'product_del_flg',
                'description' => 'admin.product.product_csv.delete_flag_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.product_image_col') => [
                'id' => 'product_image',
                'description' => 'admin.product.product_csv.product_image_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.category_col') => [
                'id' => 'product_category',
                'description' => 'admin.product.product_csv.category_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.tag_col') => [
                'id' => 'product_tag',
                'description' => 'admin.product.product_csv.tag_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.sale_type_col') => [
                'id' => 'sale_type',
                'description' => 'admin.product.product_csv.sale_type_description',
                'required' => true,
            ],
            trans('admin.product.product_csv.class_category1_col') => [
                'id' => 'class_category1',
                'description' => 'admin.product.product_csv.class_category1_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.class_category2_col') => [
                'id' => 'class_category2',
                'description' => 'admin.product.product_csv.class_category2_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.delivery_duration_col') => [
                'id' => 'delivery_date',
                'description' => 'admin.product.product_csv.delivery_duration_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.product_code_col') => [
                'id' => 'product_code',
                'description' => 'admin.product.product_csv.product_code_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.stock_col') => [
                'id' => 'stock',
                'description' => 'admin.product.product_csv.stock_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.stock_unlimited_col') => [
                'id' => 'stock_unlimited',
                'description' => 'admin.product.product_csv.stock_unlimited_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.sale_limit_col') => [
                'id' => 'sale_limit',
                'description' => 'admin.product.product_csv.sale_limit_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.normal_price_col') => [
                'id' => 'price01',
                'description' => 'admin.product.product_csv.normal_price_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.sale_price_col') => [
                'id' => 'price02',
                'description' => 'admin.product.product_csv.sale_price_description',
                'required' => true,
            ],
            trans('admin.product.product_csv.delivery_fee_col') => [
                'id' => 'delivery_fee',
                'description' => 'admin.product.product_csv.delivery_fee_description',
                'required' => false,
            ],
            trans('admin.product.product_csv.tax_rate_col') => [
                'id' => 'tax_rate',
                'description' => 'admin.product.product_csv.tax_rate_description',
                'required' => false,
            ],
        ];

        foreach ($locales as $locale) {
            $columns[trans('admin.product.product_csv.product_name_col') . "($locale)"] = [
                'id' => 'name_' . $locale,
                'description' => 'admin.product.product_csv.product_name_description',
                'required' => false,
            ];
            $columns[trans('admin.product.product_csv.description_list_col') . "($locale)"] = [
                'id' => 'description_list_' . $locale,
                'description' => 'admin.product.product_csv.description_list_description',
                'required' => false,
            ];
            $columns[trans('admin.product.product_csv.description_detail_col') . "($locale)"] = [
                'id' => 'description_detail_' . $locale,
                'description' => 'admin.product.product_csv.description_detail_description',
                'required' => false,
            ];
            $columns[trans('admin.product.product_csv.free_area_col') . "($locale)"] = [
                'id' => 'free_area_' . $locale,
                'description' => 'admin.product.product_csv.free_area_description',
                'required' => false,
            ];
        }

        return $columns;
    }
}
