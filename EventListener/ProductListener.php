<?php

namespace Plugin\MultiLingual\EventListener;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Csv;
use Eccube\Entity\ExportCsvRow;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\MultiLingual\Entity\LocaleProduct;
use Plugin\MultiLingual\Repository\LocaleProductRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProductListener implements EventSubscriberInterface
{
    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LocaleProductRepository
     */
    protected $localeProductRepository;

    public function __construct(
        EccubeConfig            $eccubeConfig,
        EntityManagerInterface  $entityManager,
        LocaleProductRepository $localeProductRepository
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->entityManager = $entityManager;
        $this->localeProductRepository = $localeProductRepository;
    }

    public static function getSubscribedEvents(): array
    {
        // LocaleProductの削除については、Productのcascade={"remove"}指定により、
        // Product削除時に自動で削除される。
        // ProductTrait参照。
        return [
            EccubeEvents::ADMIN_PRODUCT_EDIT_COMPLETE => 'onAdminProductEditComplete',
            EccubeEvents::ADMIN_PRODUCT_CSV_EXPORT => 'onAdminProductCsvExport',
        ];
    }

    public function onAdminProductEditComplete(EventArgs $event): void
    {
        /** @var FormInterface $form */
        $form = $event->getArgument('form');

        /** @var Product $Product */
        $Product = $event->getArgument('Product');

        // Productの新規作成と編集は区別されずここにくる。

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            /** @var LocaleProduct $LocaleProduct */
            $LocaleProduct = $this->localeProductRepository->findOneBy([
                'parent_id' => $Product->getId(),
                'locale' => $locale,
            ]);
            if (!$LocaleProduct) {
                // 新規作成時
                $LocaleProduct = new LocaleProduct;
            }

            $productName = $form->get('name_' . $locale)->getData();
            $descriptionDetail = $form->get('description_detail_' . $locale)->getData();
            $descriptionList = $form->get('description_list_' . $locale)->getData();
            $freeArea = $form->get('free_area_' . $locale)->getData();

            $LocaleProduct->setName($productName);
            $LocaleProduct->setParentId($Product->getId());
            $LocaleProduct->setProduct($Product);
            $LocaleProduct->setDescriptionDetail($descriptionDetail);
            $LocaleProduct->setDescriptionList($descriptionList);
            $LocaleProduct->setFreeArea($freeArea);
            $LocaleProduct->setLocale($locale);

            $this->entityManager->persist($LocaleProduct);
            $this->entityManager->flush();
        }
    }

    public function onAdminProductCsvExport(EventArgs $event): void
    {
        /** @var ProductClass $ProductClass */
        $ProductClass = $event->getArgument('ProductClass');

        /** @var Csv $Csv */
        $Csv = $event->getArgument('Csv');

        /** @var ExportCsvRow $ExportCsvRow */
        $ExportCsvRow = $event->getArgument('ExportCsvRow');

        if (!$ExportCsvRow->isDataNull() ||
            $Csv->getEntityName() != addslashes(LocaleProduct::class)) {
            return;
        }

        // Localeのカテゴリ名を出力する

        // $Csvのfield_nameから対象localeを取得
        $Csv->getFieldName();
        if (!preg_match('/^(.+)_([^_]+)$/', $Csv->getFieldName(), $matches)) {
            return;
        }
        $field = $matches[1];
        $locale = $matches[2];

        /** @var LocaleProduct $LocaleProduct */
        $LocaleProduct = $this->localeProductRepository->findOneBy([
            'locale' => $locale,
            'parent_id' => $ProductClass->getProduct()->getId(),
        ]);
        if (!$LocaleProduct) {
            return;
        }

        switch ($field) {
            case 'name':
                $value = $LocaleProduct->getName();
                break;
            case 'description_list':
                $value = $LocaleProduct->getDescriptionList();
                break;
            case 'description_detail':
                $value = $LocaleProduct->getDescriptionDetail();
                break;
            case 'free_area':
                $value = $LocaleProduct->getFreeArea();
                break;
            default:
                return;
        }
        $ExportCsvRow->setData($value);
    }
}
