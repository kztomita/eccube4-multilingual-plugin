<?php

namespace Plugin\MultiLingual\EventListener;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\ClassCategory;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\MultiLingual\Entity\LocaleClassCategory;
use Plugin\MultiLingual\Repository\LocaleClassCategoryRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;

class ClassCategoryListener implements EventSubscriberInterface
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
     * @var LocaleClassCategoryRepository
     */
    protected $localeClassCategoryRepository;

    public function __construct(
        EccubeConfig $eccubeConfig,
        EntityManagerInterface $entityManager,
        LocaleClassCategoryRepository $localeClassCategoryRepository
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->entityManager = $entityManager;
        $this->localeClassCategoryRepository = $localeClassCategoryRepository;
    }

    public static function getSubscribedEvents(): array
    {
        // ClassCategory新規作成、更新時のイベント処理を登録。
        //
        // LocaleClassCategoryの削除については、ClassCategoryのcascade={"remove"}指定により、
        // ClassCategory削除時に自動で削除される。
        // ClassCategoryTrait参照。

        return [
            EccubeEvents::ADMIN_PRODUCT_CLASS_CATEGORY_INDEX_COMPLETE => 'onAdminProductClassCategoryIndexComplete',
        ];
    }

    public function onAdminProductClassCategoryIndexComplete(EventArgs $event): void
    {
        if ($event->hasArgument('editForm')) {
            $this->onUpdateClassCategory($event);
        } else {
            $this->onCreateClassCategory($event);
        }
    }

    private function onCreateClassCategory(EventArgs $event): void
    {
        /** @var FormInterface $form */
        $form = $event->getArgument('form');

        /** @var ClassCategory $TargetClassCategory */
        $TargetClassCategory = $event->getArgument('TargetClassCategory');

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            $LocaleClassCategory = new LocaleClassCategory;
            $LocaleClassCategory->setParentId($TargetClassCategory->getId());
            $LocaleClassCategory->setClassCategory($TargetClassCategory);
            $LocaleClassCategory->setLocale($locale);
            $LocaleClassCategory->setName($form->get('name_' . $locale)->getData());
            $this->entityManager->persist($LocaleClassCategory);
            $this->entityManager->flush();
        }
    }

    private function onUpdateClassCategory(EventArgs $event): void
    {
        /** @var FormInterface $editForm */
        $editForm = $event->getArgument('editForm');

        /** @var ClassCategory $TargetClassCategory */
        $TargetClassCategory = $event->getArgument('TargetClassCategory');

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            $ClassCategoryName = $editForm->get('name_' . $locale)->getData();

            /** @var LocaleClassCategory $LocaleClassCategory */
            $LocaleClassCategory = $this->localeClassCategoryRepository->findOneBy([
                'locale' => $locale,
                'parent_id' => $TargetClassCategory->getId(),
            ]);
            if (!$LocaleClassCategory) {
                continue;
            }
            $LocaleClassCategory->setName($ClassCategoryName);
            $this->entityManager->persist($LocaleClassCategory);
            $this->entityManager->flush();
        }
    }
}
