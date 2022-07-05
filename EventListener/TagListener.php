<?php

namespace Plugin\MultiLingual\EventListener;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Tag;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\MultiLingual\Entity\LocaleTag;
use Plugin\MultiLingual\Repository\LocaleTagRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;

class TagListener implements EventSubscriberInterface
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
     * @var LocaleTagRepository
     */
    protected $localeTagRepository;

    public function __construct(
        EccubeConfig $eccubeConfig,
        EntityManagerInterface $entityManager,
        LocaleTagRepository $localeTagRepository
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->entityManager = $entityManager;
        $this->localeTagRepository = $localeTagRepository;
    }

    public static function getSubscribedEvents(): array
    {
        // Tag新規作成、更新時のイベント処理を登録。
        //
        // LocaleTagの削除については、Tagのcascade={"remove"}指定により、
        // Tag削除時に自動で削除される。
        // TagTrait参照。

        return [
            EccubeEvents::ADMIN_PRODUCT_TAG_INDEX_COMPLETE => 'onAdminProductTagIndexComplete',
        ];
    }

    public function onAdminProductTagIndexComplete(EventArgs $event): void
    {
        /** @var FormInterface $form */
        $form = $event->getArgument('form');

        if ($form->getName() == 'admin_product_tag') {
            $this->onCreateTag($event);
        } else {
            $this->onUpdateTag($event);
        }
    }

    private function onCreateTag(EventArgs $event): void
    {
        /** @var FormInterface $form */
        $form = $event->getArgument('form');

        /** @var Tag $TargetTag */
        $TargetTag = $event->getArgument('Tag');

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            $LocaleTag = new LocaleTag;
            $LocaleTag->setParentId($TargetTag->getId());
            $LocaleTag->setTag($TargetTag);
            $LocaleTag->setLocale($locale);
            $LocaleTag->setName($form->get('name_' . $locale)->getData());
            $this->entityManager->persist($LocaleTag);
            $this->entityManager->flush();
        }
    }

    private function onUpdateTag(EventArgs $event): void
    {
        /** @var FormInterface $form */
        $form = $event->getArgument('form');

        /** @var Tag $TargetTag */
        $TargetTag = $event->getArgument('Tag');

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            $TagName = $form->get('name_' . $locale)->getData();

            /** @var LocaleTag $LocaleTag */
            $LocaleTag = $this->localeTagRepository->findOneBy([
                'locale' => $locale,
                'parent_id' => $TargetTag->getId(),
            ]);
            if (!$LocaleTag) {
                continue;
            }
            $LocaleTag->setName($TagName);
            $this->entityManager->persist($LocaleTag);
            $this->entityManager->flush();
        }
    }
}
