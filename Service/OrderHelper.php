<?php

namespace Plugin\MultiLingual\Service;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;

class OrderHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface  $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    public function setLocaleNameAsOrder(Order $order)
    {
        foreach ($order->getOrderItems() as $orderItem) {
            $this->setLocaleNameAsOrderItem($orderItem);
        }
    }

    public function setLocaleNameAsOrderItem(OrderItem $item)
    {
        $Product = $item->getProduct();
        if (!$Product) {
            return null;
        }

        // 購入時のlocaleでの名称を設定
        $item->setLocaleProductName($Product->getLocaleField('name'));

        $ProductClass = $item->getProductClass();
        if ($ProductClass) {
            $Category1 = $ProductClass->getClassCategory1();
            if ($Category1) {
                if ($Category1->getClassName()) {
                    $item->setLocaleClassName1($Category1->getClassName()->getLocaleField('name'));
                }
                $item->setLocaleClassCategoryName1($Category1->getLocaleField('name'));
            }
            $Category2 = $ProductClass->getClassCategory2();
            if ($Category2) {
                if ($Category2->getClassName()) {
                    $item->setLocaleClassName2($Category2->getClassName()->getLocaleField('name'));
                }
                $item->setLocaleClassCategoryName2($Category2->getLocaleField('name'));
            }
        }

        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }
}
