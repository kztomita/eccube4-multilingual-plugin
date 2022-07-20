<?php

namespace Plugin\MultiLingual\Controller\Admin\Product;

use Eccube\Controller\Admin\AbstractCsvImportController;
use Eccube\Form\Type\Admin\CsvImportType;
use Eccube\Util\CacheUtil;
use Plugin\MultiLingual\Service\Csv\CategoryCsvImporter;
use Plugin\MultiLingual\Service\Csv\ProductCsvImporter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CsvImportController extends AbstractCsvImportController
{
    private $errors = [];

    public function __construct()
    {
    }

    /**
     * 登録、更新時のエラー画面表示
     */
    protected function addErrors($message)
    {
        $this->errors[] = $message;
    }

    /**
     * @return array
     */
    protected function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return boolean
     */
    protected function hasErrors(): bool
    {
        return count($this->getErrors()) > 0;
    }

    /**
     * デフォルトの商品登録CSVアップロードのコントローラを差し替える。
     * Route annotationはroute名も含めて、CsvImporterControllerと全く同じ。
     *
     * @Route("/%eccube_admin_route%/product/product_csv_upload", name="admin_product_csv_import")
     * @Template("@admin/Product/csv_product.twig")
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function csvProduct(Request $request, ProductCsvImporter $csvImporter)
    {
        $form = $this->formFactory->createBuilder(CsvImportType::class)->getForm();

        $headers = $csvImporter->getCsvHeader();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $formFile = $form['import_file']->getData();
                if (!empty($formFile)) {
                    log_info('商品CSV登録開始');

                    $data = $this->getImportData($formFile);
                    if ($data === false) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));

                        return $this->renderWithError($form, $headers, false);
                    }

                    if (!$csvImporter->import($data)) {
                        foreach ($csvImporter->getErrors() as $message) {
                            $this->addErrors($message);
                            return $this->renderWithError($form, $headers, false);
                        }
                    }

                    log_info('商品CSV登録完了');

                    $message = 'admin.common.csv_upload_complete';
                    $this->session->getFlashBag()->add('eccube.admin.success', $message);
                }
            }
        }

        return $this->renderWithError($form, $headers);
    }

    /**
     * デフォルトのカテゴリCSVアップロードのコントローラを差し替える。
     * Route annotationはroute名も含めて、CsvImporterControllerと全く同じ。
     *
     * @Route("/%eccube_admin_route%/product/category_csv_upload", name="admin_product_category_csv_import")
     * @Template("@admin/Product/csv_category.twig")
     */
    public function csvCategory(Request $request, CategoryCsvImporter $csvImporter)
    {
        $form = $this->formFactory->createBuilder(CsvImportType::class)->getForm();

        $headers = $csvImporter->getCsvHeader();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $formFile = $form['import_file']->getData();
                if (!empty($formFile)) {
                    log_info('カテゴリCSV登録開始');

                    $data = $this->getImportData($formFile);
                    if ($data === false) {
                        $this->addErrors(trans('admin.common.csv_invalid_format'));
                        return $this->renderWithError($form, $headers, false);
                    }

                    if (!$csvImporter->import($data)) {
                        foreach ($csvImporter->getErrors() as $message) {
                            $this->addErrors($message);
                            return $this->renderWithError($form, $headers, false);
                        }
                    }
                    log_info('カテゴリCSV登録完了');

                    $message = 'admin.common.csv_upload_complete';
                    $this->session->getFlashBag()->add('eccube.admin.success', $message);
                }
            }
        }

        return $this->renderWithError($form, $headers);
    }

    /**
     * 登録、更新時のエラー画面表示
     *
     * @param FormInterface $form
     * @param array $headers
     * @param bool $rollback
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\ConnectionException
     */
    protected function renderWithError(FormInterface $form, array $headers, bool $rollback = true): array
    {
        if ($this->hasErrors()) {
            if ($rollback) {
                $this->entityManager->getConnection()->rollback();
            }
        }

        $this->removeUploadedFile();

        return [
            'form' => $form->createView(),
            'headers' => $headers,
            'errors' => $this->errors,
        ];
    }
}