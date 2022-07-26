<?php

namespace Plugin\MultiLingual\EventListener;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Category;
use Eccube\Entity\Csv;
use Eccube\Entity\ExportCsvRow;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\MultiLingual\Entity\LocaleCategory;
use Plugin\MultiLingual\Repository\LocaleCategoryRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;

class CategoryListener implements EventSubscriberInterface
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
     * @var LocaleCategoryRepository
     */
    protected $localeCategoryRepository;

    public function __construct(
        EccubeConfig $eccubeConfig,
        EntityManagerInterface $entityManager,
        LocaleCategoryRepository $localeCategoryRepository
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->entityManager = $entityManager;
        $this->localeCategoryRepository = $localeCategoryRepository;
    }

    public static function getSubscribedEvents(): array
    {
        // Category新規作成、更新時のイベント処理を登録。
        //
        // LocaleCategoryの削除については、Categoryのcascade={"remove"}指定により、
        // Category削除時に自動で削除される。
        // CategoryTrait参照。

        return [
            EccubeEvents::ADMIN_PRODUCT_CATEGORY_INDEX_COMPLETE => 'onAdminProductCategoryIndexComplete',
            EccubeEvents::ADMIN_PRODUCT_CATEGORY_CSV_EXPORT => 'onAdminProductCategoryCsvExport',
        ];
    }

    public function onAdminProductCategoryIndexComplete(EventArgs $event): void
    {
        if ($event->hasArgument('editForm')) {
            $this->onUpdateCategory($event);
        } else {
            $this->onCreateCategory($event);
        }
    }

    private function onCreateCategory(EventArgs $event): void
    {
        /** @var FormInterface $form */
        $form = $event->getArgument('form');

        /** @var Category $TargetCategory */
        $TargetCategory = $event->getArgument('TargetCategory');

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            $LocaleCategory = new LocaleCategory;
            $LocaleCategory->setParentId($TargetCategory->getId());
            $LocaleCategory->setCategory($TargetCategory);
            $LocaleCategory->setLocale($locale);
            $LocaleCategory->setName($form->get('name_' . $locale)->getData());
            $this->entityManager->persist($LocaleCategory);
            $this->entityManager->flush();
        }
    }

    private function onUpdateCategory(EventArgs $event): void
    {
        /** @var FormInterface $editForm */
        $editForm = $event->getArgument('editForm');

        /** @var Category $TargetCategory */
        $TargetCategory = $event->getArgument('TargetCategory');

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            $categoryName = $editForm->get('name_' . $locale)->getData();

            /** @var LocaleCategory $LocaleCategory */
            $LocaleCategory = $this->localeCategoryRepository->findOneBy([
                'locale' => $locale,
                'parent_id' => $TargetCategory->getId(),
            ]);
            if (!$LocaleCategory) {
                continue;
            }
            $LocaleCategory->setName($categoryName);
            $this->entityManager->persist($LocaleCategory);
            $this->entityManager->flush();
        }
    }

    public function onAdminProductCategoryCsvExport(EventArgs $event): void
    {
        /** @var Category $Category */
        $Category = $event->getArgument('Category');

        /** @var Csv $Csv */
        $Csv = $event->getArgument('Csv');

        /** @var ExportCsvRow $ExportCsvRow */
        $ExportCsvRow = $event->getArgument('ExportCsvRow');

        if (!$ExportCsvRow->isDataNull() ||
            $Csv->getEntityName() != addslashes(LocaleCategory::class)) {
            return;
        }

        // Localeのカテゴリ名を出力する

        // $Csvのfield_nameから対象localeを取得
        $Csv->getFieldName();
        if (!preg_match('/^name_(.+)$/', $Csv->getFieldName(), $matches)) {
            return;
        }
        $locale = $matches[1];

        /** @var LocaleCategory $LocaleCategory */
        $LocaleCategory = $this->localeCategoryRepository->findOneBy([
            'locale' => $locale,
            'parent_id' => $Category->getId(),
        ]);
        if (!$LocaleCategory) {
            return;
        }

        $ExportCsvRow->setData($LocaleCategory->getName());
    }
}
