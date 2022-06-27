<?php

use Eccube\Entity\Master\ProductListMax;
use Eccube\Entity\Master\ProductListOrderBy;
use Plugin\MultiLingual\Entity\Master\LocaleProductListMax;
use Plugin\MultiLingual\Entity\Master\LocaleProductListOrderBy;

return [
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
