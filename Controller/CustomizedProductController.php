<?php

namespace Plugin\MultiLingual\Controller;


use Plugin\MultiLingual\Common\LocaleHelper;
use Plugin\MultiLingual\Entity\LocaleCategory;

class CustomizedProductController extends \Eccube\Controller\ProductController
{
    /**
     * ページタイトルの設定をカスタマイズ
     *
     * @param  null|array $searchData
     *
     * @return string
     */
    protected function getPageTitle($searchData)
    {
        if (isset($searchData['name']) && !empty($searchData['name'])) {
            return trans('front.product.search_result');
        } elseif (isset($searchData['category_id']) && $searchData['category_id']) {
            $locale = LocaleHelper::getCurrentRequestLocale();
            $Category = $searchData['category_id'];
            $LocaleCategory = $this->entityManager
                ->getRepository(LocaleCategory::class)
                ->findOneBy([
                    'parent_id' => $Category->getId(),
                    'locale' => $locale,
                ]);
            if ($LocaleCategory) {
                return $LocaleCategory->getName();
            } else {
                return $searchData['category_id']->getName();
            }
        } else {
            return trans('front.product.all_products');
        }
    }
}