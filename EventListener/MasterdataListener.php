<?php

namespace Plugin\MultiLingual\EventListener;

use Eccube\Common\EccubeConfig;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\MultiLingual\Common\LocaleHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;

class MasterdataListener implements EventSubscriberInterface
{
    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EccubeConfig            $eccubeConfig,
        EntityManagerInterface  $entityManager
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        // マスタデータのLocale情報の削除については、マスタデータEntityの
        // cascade={"remove"}指定によりマスタデータEntity削除時に自動で削除される。
        // 各マスタデータのTrait拡張を参照。
        return [
            EccubeEvents::ADMIN_SETTING_SYSTEM_MASTERDATA_EDIT_COMPLETE => 'onMasterdataEditComplete',
        ];
    }

    public function onMasterdataEditComplete(EventArgs $event): void
    {
        /** @var FormInterface $form */
        $form = $event->getArgument('form');

        $masterdataName = $form->get('masterdata_name')->getData();
        $entityName = str_replace('-', '\\', $masterdataName);

        // Traitで拡張したgetLocales()が存在するかチェック
        if (!LocaleHelper::hasLocaleFeature(($entityName))) {
            // Localeの存在しないMasterデータ
            return;
        }

        $localeClass = $entityName::getLocaleClass();
        $repository = $this->entityManager->getRepository($entityName);
        $localeRepository = $this->entityManager->getRepository($localeClass);

        $data = $form->get('data')->getData();
        $locales = $this->eccubeConfig['multi_lingual_locales'];

        foreach ($data as $entry) {
            /* $entry is ['id' => xx, 'name' => xxxx, 'name_en' => xxxx, ...] */
            if ($entry['id'] === '') {   // 削除か末尾の空エントリ
                continue;
            }
            $id = intval($entry['id']);

            $Parent = $repository->find($id);
            if (!$Parent) {
                continue;
            }

            foreach ($locales as $locale) {
                if (!array_key_exists('name_' . $locale, $entry)) {
                    continue;
                }
                $LocaleEntity = $localeRepository->findOneBy([
                    'parent_id' => $id,
                    'locale' => $locale,
                ]);
                if (!$LocaleEntity) {
                    // 新規作成時
                    $LocaleEntity = new $localeClass;
                    $LocaleEntity->setParentId($id);
                    $LocaleEntity->setParent($Parent);
                    $LocaleEntity->setLocale($locale);
                }
                $LocaleEntity->setName($entry['name_' . $locale] ?? '');

                $this->entityManager->persist($LocaleEntity);
            }
            $this->entityManager->flush();
        }
    }
}
