<?php

namespace Plugin\MultiLingual\Common;

class Locale
{
    public static function getCurrentRequestLocale(): string
    {
        if(isset($GLOBALS['request']) && $GLOBALS['request']) {
            $locale = $GLOBALS['request']->getLocale();
        } else {
            $locale = env('ECCUBE_LOCALE', 'ja_JP');
        }

        return $locale;
    }

    /**
     * Entityがxxxを持っているかどうかを返す。
     *
     * @param object|string $object  Entityのオブジェクトかクラス名
     * @return bool
     */
    public static function hasLocaleFeature($object): bool
    {
        return method_exists($object, 'getLocales');
    }
}