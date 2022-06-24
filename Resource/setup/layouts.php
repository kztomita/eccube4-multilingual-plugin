<?php

use Eccube\Entity\Page;

return [
    [
        'name' => 'トップページ用レイアウト - Locale',
        'pages' => [
            [
                'name'  => 'TOPページ - Locale',
                'url'   => 'homepage_locale',
                'file_name' => 'index',
                'edit_type' => Page::EDIT_TYPE_DEFAULT,
            ],
        ],
        'src_name' => 'トップページ用レイアウト',
    ],
    [
        'name' => '下層ページ用レイアウト - Locale',
        'pages' => [
            [
                'name'  => '商品一覧ページ - Locale',
                'url'   => 'product_list_locale',
                'file_name' => 'Product/list',
                'edit_type' => Page::EDIT_TYPE_DEFAULT,
            ],
            [
                'name'  => '商品詳細ページ - Locale',
                'url'   => 'product_detail_locale',
                'file_name' => 'Product/detail',
                'edit_type' => Page::EDIT_TYPE_DEFAULT,
            ]
        ],
        'src_name' => '下層ページ用レイアウト',
    ],
];