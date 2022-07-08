<?php

namespace Plugin\MultiLingual\EventListener;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Payment;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\MultiLingual\Entity\LocalePayment;
use Plugin\MultiLingual\Repository\LocalePaymentRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;

class PaymentListener implements EventSubscriberInterface
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
     * @var LocalePaymentRepository
     */
    protected $localePaymentRepository;

    public function __construct(
        EccubeConfig $eccubeConfig,
        EntityManagerInterface $entityManager,
        LocalePaymentRepository $localePaymentRepository
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->entityManager = $entityManager;
        $this->localePaymentRepository = $localePaymentRepository;
    }

    public static function getSubscribedEvents(): array
    {
        // Payment新規作成、更新時のイベント処理を登録。
        //
        // LocalePaymentの削除については、Paymentのcascade={"remove"}指定により、
        // Payment削除時に自動で削除される。
        // PaymentTrait参照。

        return [
            EccubeEvents::ADMIN_SETTING_SHOP_PAYMENT_EDIT_COMPLETE => 'onAdminSettingShopPaymentEditComplete',
        ];
    }

    public function onAdminSettingShopPaymentEditComplete(EventArgs $event): void
    {
        /** @var FormInterface $form */
        $form = $event->getArgument('form');

        /** @var Payment $Payment */
        $Payment = $event->getArgument('Payment');

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            /** @var LocalePayment $LocalePayment */
            $LocalePayment = $this->localePaymentRepository->findOneBy([
                'locale' => $locale,
                'parent_id' => $Payment->getId(),
            ]);
            if (!$LocalePayment) {
                $LocalePayment = new LocalePayment;
                $LocalePayment->setParentId($Payment->getId());
                $LocalePayment->setPayment($Payment);
                $LocalePayment->setLocale($locale);
            }
            $LocalePayment->setMethod($form->get('method_' . $locale)->getData());
            $this->entityManager->persist($LocalePayment);
            $this->entityManager->flush();
        }
    }
}
