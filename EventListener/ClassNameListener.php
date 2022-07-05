<?php

namespace Plugin\MultiLingual\EventListener;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\ClassName;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\MultiLingual\Entity\LocaleClassName;
use Plugin\MultiLingual\Repository\LocaleClassNameRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;

class ClassNameListener implements EventSubscriberInterface
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
     * @var LocaleClassNameRepository
     */
    protected $localeClassNameRepository;

    public function __construct(
        EccubeConfig $eccubeConfig,
        EntityManagerInterface $entityManager,
        LocaleClassNameRepository $localeClassNameRepository
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->entityManager = $entityManager;
        $this->localeClassNameRepository = $localeClassNameRepository;
    }

    public static function getSubscribedEvents(): array
    {
        // ClassName新規作成、更新時のイベント処理を登録。
        //
        // LocaleClassNameの削除については、ClassNameのcascade={"remove"}指定により、
        // ClassName削除時に自動で削除される。
        // ClassNameTrait参照。

        return [
            EccubeEvents::ADMIN_PRODUCT_CLASS_NAME_INDEX_COMPLETE => 'onAdminProductClassNameIndexComplete',
        ];
    }

    public function onAdminProductClassNameIndexComplete(EventArgs $event): void
    {
        if ($event->hasArgument('editForm')) {
            $this->onUpdateClassName($event);
        } else {
            $this->onCreateClassName($event);
        }
    }

    private function onCreateClassName(EventArgs $event): void
    {
        /** @var FormInterface $form */
        $form = $event->getArgument('form');

        /** @var ClassName $TargetClassName */
        $TargetClassName = $event->getArgument('TargetClassName');

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            $LocaleClassName = new LocaleClassName;
            $LocaleClassName->setParentId($TargetClassName->getId());
            $LocaleClassName->setClassName($TargetClassName);
            $LocaleClassName->setLocale($locale);
            $LocaleClassName->setName($form->get('name_' . $locale)->getData());
            $this->entityManager->persist($LocaleClassName);
            $this->entityManager->flush();
        }
    }

    private function onUpdateClassName(EventArgs $event): void
    {
        /** @var FormInterface $editForm */
        $editForm = $event->getArgument('editForm');

        /** @var ClassName $TargetClassName */
        $TargetClassName = $event->getArgument('TargetClassName');

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            $ClassNameName = $editForm->get('name_' . $locale)->getData();

            /** @var LocaleClassName $LocaleClassName */
            $LocaleClassName = $this->localeClassNameRepository->findOneBy([
                'locale' => $locale,
                'parent_id' => $TargetClassName->getId(),
            ]);
            if (!$LocaleClassName) {
                continue;
            }
            $LocaleClassName->setName($ClassNameName);
            $this->entityManager->persist($LocaleClassName);
            $this->entityManager->flush();
        }
    }
}
