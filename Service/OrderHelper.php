<?php

namespace Plugin\MultiLingual\Service;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Eccube\Entity\Shipping;
use Eccube\Repository\DeliveryTimeRepository;

class OrderHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var DeliveryTimeRepository
     */
    private $deliveryTimeRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DeliveryTimeRepository $deliveryTimeRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->deliveryTimeRepository = $deliveryTimeRepository;
    }

    public function setLocaleNameAsOrder(Order $Order)
    {
        $Payment = $Order->getPayment();
        if ($Payment) {
            $Order->setLocalePaymentMethod($Payment->getLocaleField('method'));
        }
        $this->entityManager->persist($Order);
        $this->entityManager->flush();

        foreach ($Order->getOrderItems() as $orderItem) {
            $this->setLocaleNameAsOrderItem($orderItem);
        }

        foreach ($Order->getShippings() as $shipping) {
            $this->setLocaleNameAsShipping($shipping);
        }
    }

    public function setLocaleNameAsOrderItem(OrderItem $Item)
    {
        $Product = $Item->getProduct();
        if (!$Product) {
            return null;
        }

        // 購入時のlocaleでの名称を設定
        $Item->setLocaleProductName($Product->getLocaleField('name'));

        $ProductClass = $Item->getProductClass();
        if ($ProductClass) {
            $Category1 = $ProductClass->getClassCategory1();
            if ($Category1) {
                if ($Category1->getClassName()) {
                    $Item->setLocaleClassName1($Category1->getClassName()->getLocaleField('name'));
                }
                $Item->setLocaleClassCategoryName1($Category1->getLocaleField('name'));
            }
            $Category2 = $ProductClass->getClassCategory2();
            if ($Category2) {
                if ($Category2->getClassName()) {
                    $Item->setLocaleClassName2($Category2->getClassName()->getLocaleField('name'));
                }
                $Item->setLocaleClassCategoryName2($Category2->getLocaleField('name'));
            }
        }

        $this->entityManager->persist($Item);
        $this->entityManager->flush();
    }

    public function setLocaleNameAsShipping(Shipping $Shipping)
    {
        $Delivery = $Shipping->getDelivery();
        if ($Delivery) {
            $Shipping->setLocaleDeliveryName($Delivery->getLocaleField('name'));
        }

        if ($Shipping->getTimeId()) {
            $DeliveryTime = $this->deliveryTimeRepository->find($Shipping->getTimeId());
            if ($DeliveryTime) {
                $Shipping->setLocaleDeliveryTime($DeliveryTime->getLocaleField('delivery_time'));
            }
        }

        $this->entityManager->persist($Shipping);
        $this->entityManager->flush();
    }
}
