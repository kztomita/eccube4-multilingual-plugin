<?php

use Eccube\Entity\Master\CustomerOrderStatus;
use Eccube\Entity\Master\Job;
use Eccube\Entity\Master\Pref;
use Eccube\Entity\Master\ProductListMax;
use Eccube\Entity\Master\ProductListOrderBy;
use Eccube\Entity\Master\Sex;
use Plugin\MultiLingual\Entity\Master\LocaleCustomerOrderStatus;
use Plugin\MultiLingual\Entity\Master\LocaleJob;
use Plugin\MultiLingual\Entity\Master\LocalePref;
use Plugin\MultiLingual\Entity\Master\LocaleProductListMax;
use Plugin\MultiLingual\Entity\Master\LocaleProductListOrderBy;
use Plugin\MultiLingual\Entity\Master\LocaleSex;

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
    [
        'entity' => Sex::class,
        'locale_entity' => LocaleSex::class,
        'translates' => [
            '男性' => [
                'en' => 'Male',
            ],
            '女性' => [
                'en' => 'Female',
            ],
        ],
    ],
    [
        'entity' => Job::class,
        'locale_entity' => LocaleJob::class,
        'translates' => [
            '公務員' => [
                'en' => 'Public officer',
            ],
            'コンサルタント' => [
                'en' => 'Consultant',
            ],
            'コンピューター関連技術職' => [
                'en' => 'Engineer(IT)',
            ],
            'コンピューター関連以外の技術職' => [
                'en' => 'Engineer(Non IT)',
            ],
            '金融関係' => [
                'en' => 'Financial services',
            ],
            '医師' => [
                'en' => 'Medical doctor',
            ],
            '弁護士' => [
                'en' => 'Lawyer',
            ],
            '総務・人事・事務' => [
                'en' => 'General manager',
            ],
            '営業・販売' => [
                'en' => 'Sales',
            ],
            '研究・開発' => [
                'en' => 'Research & Development',
            ],
            '広報・宣伝' => [
                'en' => 'Publicity',
            ],
            '企画・マーケティング' => [
                'en' => 'Marketing',
            ],
            'デザイン関係' => [
                'en' => 'Designer',
            ],
            '会社経営・役員' => [
                'en' => 'Business executive',
            ],
            '出版・マスコミ関係' => [
                'en' => 'Mass media',
            ],
            '学生・フリーター' => [
                'en' => 'Student',
            ],
            '主婦' => [
                'en' => 'Housewife',
            ],
            'その他' => [
                'en' => 'Others',
            ],
        ],
    ],
    [
        'entity' => Pref::class,
        'locale_entity' => LocalePref::class,
        'translates' => [
            '北海道' => [
                'en' => 'hokkaido',
            ],
            '青森県' => [
                'en' => 'aomori',
            ],
            '岩手県' => [
                'en' => 'iwate',
            ],
            '宮城県' => [
                'en' => 'miyagi',
            ],
            '秋田県' => [
                'en' => 'akita',
            ],
            '山形県' => [
                'en' => 'yamagata',
            ],
            '福島県' => [
                'en' => 'fukushima',
            ],
            '茨城県' => [
                'en' => 'ibaraki',
            ],
            '栃木県' => [
                'en' => 'tochigi',
            ],
            '群馬県' => [
                'en' => 'gunma',
            ],
            '埼玉県' => [
                'en' => 'saitama',
            ],
            '千葉県' => [
                'en' => 'chiba',
            ],
            '東京都' => [
                'en' => 'tokyo',
            ],
            '神奈川県' => [
                'en' => 'kanagawa',
            ],
            '新潟県' => [
                'en' => 'niigata',
            ],
            '富山県' => [
                'en' => 'toyama',
            ],
            '石川県' => [
                'en' => 'ishikawa',
            ],
            '福井県' => [
                'en' => 'fukui',
            ],
            '山梨県' => [
                'en' => 'yamanashi',
            ],
            '長野県' => [
                'en' => 'nagano',
            ],
            '岐阜県' => [
                'en' => 'gifu',
            ],
            '静岡県' => [
                'en' => 'shizuoka',
            ],
            '愛知県' => [
                'en' => 'aichi',
            ],
            '三重県' => [
                'en' => 'mie',
            ],
            '滋賀県' => [
                'en' => 'shiga',
            ],
            '京都府' => [
                'en' => 'kyoto',
            ],
            '大阪府' => [
                'en' => 'osaka',
            ],
            '兵庫県' => [
                'en' => 'hyogo',
            ],
            '奈良県' => [
                'en' => 'nara',
            ],
            '和歌山県' => [
                'en' => 'wakayama',
            ],
            '鳥取県' => [
                'en' => 'tottori',
            ],
            '島根県' => [
                'en' => 'shimane',
            ],
            '岡山県' => [
                'en' => 'okayama',
            ],
            '広島県' => [
                'en' => 'hiroshima',
            ],
            '山口県' => [
                'en' => 'yamaguchi',
            ],
            '徳島県' => [
                'en' => 'tokushima',
            ],
            '香川県' => [
                'en' => 'kagawa',
            ],
            '愛媛県' => [
                'en' => 'ehime',
            ],
            '高知県' => [
                'en' => 'kochi',
            ],
            '福岡県' => [
                'en' => 'fukuoka',
            ],
            '佐賀県' => [
                'en' => 'saga',
            ],
            '長崎県' => [
                'en' => 'nagasaki',
            ],
            '熊本県' => [
                'en' => 'kumamoto',
            ],
            '大分県' => [
                'en' => 'oita',
            ],
            '宮崎県' => [
                'en' => 'miyazaki',
            ],
            '鹿児島県' => [
                'en' => 'kagoshima',
            ],
            '沖縄県' => [
                'en' => 'okinawa',
            ],
        ],
    ],
];
