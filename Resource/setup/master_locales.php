<?php

use Eccube\Entity\Master\CustomerOrderStatus;
use Eccube\Entity\Master\ProductListMax;
use Eccube\Entity\Master\ProductListOrderBy;
use Plugin\MultiLingual\Entity\Master\LocaleCustomerOrderStatus;
use Plugin\MultiLingual\Entity\Master\LocaleProductListMax;
use Plugin\MultiLingual\Entity\Master\LocaleProductListOrderBy;

return [
    [
        'entity' => CustomerOrderStatus::class,
        'locale_entity' => LocaleCustomerOrderStatus::class,
        'translates' => [
            '注文受付' => [
                'en' => 'Accepted',
            ],
            '注文取消し' => [
                'en' => 'Canceled',
            ],
            '発送済み' => [
                'en' => 'Shipped',
            ],
            '注文未完了' => [
                'en' => 'Incomplete',
            ],
            '返品' => [
                'en' => 'Returned',
            ],
        ],
    ],
    [
        'entity' => ProductListMax::class,
        'locale_entity' => LocaleProductListMax::class,
        'translates' => [
            '20件' => [
                'en' => '20 Items',
            ],
            '40件' => [
                'en' => '40 Items',
            ],
            '60件' => [
                'en' => '60 Items',
            ],
        ],
    ],
    [
        'entity' => ProductListOrderBy::class,
        'locale_entity' => LocaleProductListOrderBy::class,
        'translates' => [
            '価格が低い順' => [
                'en' => 'Chiepest first',
            ],
            '価格が高い順' => [
                'en' => 'Highest first',
            ],
            '新着順' => [
                'en' => 'New arrival order',
            ],
        ],
    ],
];
