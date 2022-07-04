<?php

namespace Plugin\MultiLingual\Common;

use Doctrine\Common\Collections\Criteria;
use Symfony\Component\DependencyInjection\Container;

class LocaleHelper
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
     * EntityがLocaleに関するサブEntityを持っているかどうかを返す。
     *
     * @param object|string $object  Entityのオブジェクトかクラス名
     * @return bool
     */
    public static function hasLocaleFeature($object): bool
    {
        return method_exists($object, 'getLocales');
    }

    /**
     * $Entityの指定LocaleのLocaleオブジェクトを取得し、指定フィールドの値を返す。
     * $localeを指定しなかった場合は、現在のリクエストのLocaleを使用する。
     * 該当するLocaleオブジェクトがない場合は、$Entityの同名フィールドの値を返す。
     *
     * @param object $Entity     getLocales()メソッドを持つ型のEntity
     * @param string $field      フィールド名(snake case)
     * @param string|null $locale
     * @return mixed
     */
    public static function getLocaleField($Entity, string $field, ?string $locale = null)
    {
        if (!self::hasLocaleFeature($Entity)) {
            throw new \InvalidArgumentException('$entity has no getLocales() method.');
        }

        $locale = $locale ?? self::getCurrentRequestLocale();

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('locale', $locale));

        $method = 'get' . Container::camelize($field);

        $locales = $Entity->getLocales()->matching($criteria);
        if ($locales->count() == 0) {
            return $Entity->$method();
        } else {
            return $locales[0]->$method();
        }
    }
}