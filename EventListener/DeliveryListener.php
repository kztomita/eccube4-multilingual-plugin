<?php

namespace Plugin\MultiLingual\EventListener;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Delivery;
use Eccube\Entity\DeliveryTime;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\MultiLingual\Entity\LocaleDelivery;
use Plugin\MultiLingual\Entity\LocaleDeliveryTime;
use Plugin\MultiLingual\Repository\LocaleDeliveryRepository;
use Plugin\MultiLingual\Repository\LocaleDeliveryTimeRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;

class DeliveryListener implements EventSubscriberInterface
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
     * @var LocaleDeliveryRepository
     */
    protected $localeDeliveryRepository;

    /**
     * @var LocaleDeliveryTimeRepository
     */
    protected $localeDeliveryTimeRepository;

    public function __construct(
        EccubeConfig $eccubeConfig,
        EntityManagerInterface $entityManager,
        LocaleDeliveryRepository $localeDeliveryRepository,
        LocaleDeliveryTimeRepository $localeDeliveryTimeRepository
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->entityManager = $entityManager;
        $this->localeDeliveryRepository = $localeDeliveryRepository;
        $this->localeDeliveryTimeRepository = $localeDeliveryTimeRepository;
    }

    public static function getSubscribedEvents(): array
    {
        // Delivery新規作成、更新時のイベント処理を登録。
        //
        // LocaleDeliveryの削除については、Deliveryのcascade={"remove"}指定により、
        // Delivery削除時に自動で削除される。
        // DeliveryTrait参照。

        return [
            EccubeEvents::ADMIN_SETTING_SHOP_DELIVERY_EDIT_COMPLETE => 'onAdminSettingShopDeliveryEditComplete',
        ];
    }

    public function onAdminSettingShopDeliveryEditComplete(EventArgs $event): void
    {
        /** @var FormInterface $form */
        $form = $event->getArgument('form');

        /** @var Delivery $Delivery */
        $Delivery = $event->getArgument('Delivery');

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            /** @var LocaleDelivery $LocaleDelivery */
            $LocaleDelivery = $this->localeDeliveryRepository->findOneBy([
                'locale' => $locale,
                'parent_id' => $Delivery->getId(),
            ]);
            if (!$LocaleDelivery) {
                $LocaleDelivery = new LocaleDelivery;
                $LocaleDelivery->setParentId($Delivery->getId());
                $LocaleDelivery->setDelivery($Delivery);
                $LocaleDelivery->setLocale($locale);
            }
            $LocaleDelivery->setName($form->get('name_' . $locale)->getData());
            $LocaleDelivery->setServiceName($form->get('service_name_' . $locale)->getData());
            $this->entityManager->persist($LocaleDelivery);
            $this->entityManager->flush();
        }

        // 配送時間の更新

        $deliveryTimesForm = $form->get('delivery_times');

        /*
         * DeliveryTimeExtensionで拡張したunmappedな要素にアクセスしたいので
         * getData()ではなく(*1)
         * $deliveryTimesForm[x]['delivery_time_en']
         * のようにして個別のフィールドにアクセスする。
         * (*1) $deliveryTimesForm->getData()はDeliveryTimeのCollectionになる
         *      のでunmappedなフィールドにはアクセスできない。
         *
         * 並び順が変更された場合でもgetDeliveryTimes()の結果は変更前の並びとなる。
         * $deliveryTimesForm[xxx]の並びも同様。
         * このため、並び順の変更があった場合でも、getDeliveryTimes()の結果と
         * $deliveryTimesForm[xxx]の各要素はペアとして扱える。
         */

        // $deliveryTimesForm[xxx]のindex一覧を取得
        // 削除時はindexが飛ぶため。
        $indices = [];
        foreach ($deliveryTimesForm->all() as $child) {
            $indices[] = $child->getName();
        }

        $offset = 0;
        foreach ($Delivery->getDeliveryTimes() as $DeliveryTime) {
            $index = $indices[$offset];

            /** @var DeliveryTime $DeliveryTime */
            if ($DeliveryTime->getDeliveryTime() !== $deliveryTimesForm[$index]['delivery_time']->getData()) {
                // getDeliveryTimes()の結果と$deliveryTimesForm[xxx]の並びが一致してない？
                throw new \LogicException('delivery_time name mismatch.');
            }

            foreach ($locales as $locale) {
                $input = $deliveryTimesForm[$index]['delivery_time_' . $locale]->getData();

                /** @var LocaleDeliveryTime $LocaleDeliveryTime */
                $LocaleDeliveryTime = $this->localeDeliveryTimeRepository->findOneBy([
                    'parent_id' => $DeliveryTime->getId(),
                    'locale' => $locale,
                ]);
                if (!$LocaleDeliveryTime) {
                    // 新規作成
                    $LocaleDeliveryTime = new LocaleDeliveryTime();
                    $LocaleDeliveryTime->setParentId($DeliveryTime->getId());
                    $LocaleDeliveryTime->setParentDeliveryTime($DeliveryTime);
                    $LocaleDeliveryTime->setLocale($locale);
                }
                $LocaleDeliveryTime->setDeliveryTime($input);
                $this->entityManager->persist($LocaleDeliveryTime);
                $this->entityManager->flush();
            }
            $offset++;
        }
    }
}
