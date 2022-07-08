<?php

namespace Plugin\MultiLingual\EventListener;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Delivery;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\MultiLingual\Entity\LocaleDelivery;
use Plugin\MultiLingual\Repository\LocaleDeliveryRepository;
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

    public function __construct(
        EccubeConfig $eccubeConfig,
        EntityManagerInterface $entityManager,
        LocaleDeliveryRepository $localeDeliveryRepository
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->entityManager = $entityManager;
        $this->localeDeliveryRepository = $localeDeliveryRepository;
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
    }
}
