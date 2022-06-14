<?php

namespace Plugin\MultiLingual\EventListener;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Category;
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
        return [
            EccubeEvents::ADMIN_PRODUCT_CATEGORY_INDEX_COMPLETE => 'onAdminProductCategoryIndexComplete',
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
        error_log("create");

        /** @var FormInterface $form */
        $form = $event->getArgument('form');

        /** @var Category $TargetCategory */
        $TargetCategory = $event->getArgument('TargetCategory');

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            $LocaleCategory = new LocaleCategory;
            $LocaleCategory->setCategoryId($TargetCategory->getId());
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
                'category_id' => $TargetCategory->getId(),
            ]);
            if (!$LocaleCategory) {
                continue;
            }
            $LocaleCategory->setName($categoryName);
            $this->entityManager->persist($LocaleCategory);
            $this->entityManager->flush();
        }
    }
}
