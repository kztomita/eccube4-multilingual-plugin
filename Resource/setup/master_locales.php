<?php

use Eccube\Entity\Master\ProductListOrderBy;
use Plugin\MultiLingual\Entity\Master\LocaleProductListOrderBy;

return [
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
