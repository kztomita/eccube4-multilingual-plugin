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
            ],
            [
                'name'  => 'MYページ - Locale',
                'url'   => 'mypage_locale',
                'file_name' => 'Mypage/index',
                'edit_type' => Page::EDIT_TYPE_DEFAULT,
            ],
            [
                'name'  => 'MYページ/会員登録内容変更(入力ページ) - Locale',
                'url'   => 'mypage_change_locale',
                'file_name' => 'Mypage/change',
                'edit_type' => Page::EDIT_TYPE_DEFAULT,
            ],
            [
                'name'  => 'MYページ/会員登録内容変更(完了ページ) - Locale',
                'url'   => 'mypage_change_complete_locale',
                'file_name' => 'Mypage/change_complete',
                'edit_type' => Page::EDIT_TYPE_DEFAULT,
            ],
            [
                'name'  => 'MYページ/お届け先一覧 - Locale',
                'url'   => 'mypage_delivery_locale',
                'file_name' => 'Mypage/delivery',
                'edit_type' => Page::EDIT_TYPE_DEFAULT,
            ],
            [
                'name'  => 'MYページ/お届け先一覧追加 - Locale',
                'url'   => 'mypage_delivery_new_locale',
                'file_name' => 'Mypage/delivery_edit',
                'edit_type' => Page::EDIT_TYPE_DEFAULT,
            ],
            [
                'name'  => 'MYページ/お気に入り一覧 - Locale',
                'url'   => 'mypage_favorite_locale',
                'file_name' => 'Mypage/favorite',
                'edit_type' => Page::EDIT_TYPE_DEFAULT,
            ],
            [
                'name'  => 'MYページ/購入履歴詳細 - Locale',
                'url'   => 'mypage_history_locale',
                'file_name' => 'Mypage/history',
                'edit_type' => Page::EDIT_TYPE_DEFAULT,
            ],
            [
                'name'  => 'MYページ/ログイン - Locale',
                'url'   => 'mypage_login_locale',
                'file_name' => 'Mypage/login',
                'edit_type' => Page::EDIT_TYPE_DEFAULT,
            ],
            [
                'name'  => 'MYページ/退会手続き(入力ページ) - Locale',
                'url'   => 'mypage_withdraw_locale',
                'file_name' => 'Mypage/withdraw',
                'edit_type' => Page::EDIT_TYPE_DEFAULT,
            ],
            [
                'name'  => 'MYページ/退会手続き(完了ページ) - Locale',
                'url'   => 'mypage_withdraw_complete_locale',
                'file_name' => 'Mypage/withdraw_complete',
                'edit_type' => Page::EDIT_TYPE_DEFAULT,
            ],
            [
                'name'  => '現在のカゴの中 - Locale',
                'url'   => 'cart_locale',
                'file_name' => 'Cart/index',
                'edit_type' => Page::EDIT_TYPE_DEFAULT,
            ],
        ],
        'src_name' => '下層ページ用レイアウト',
    ],
];
