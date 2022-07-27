<?php

namespace Plugin\MultiLingual\Common;

use Eccube\Common\EccubeConfig;

class LocaleText
{
    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    public function __construct(EccubeConfig $config)
    {
        $this->eccubeConfig = $config;
    }

    /**
     * services.yamlに定義されているlocaleごとのテキストを取得する。
     *
     * @param string $key
     * @param string|null $locale
     * @return string
     */
    public function getText(string $key, ?string $locale = null): string
    {
        $locale = $locale ?? LocaleHelper::getCurrentRequestLocale();

        $eccubeConfig = $this->eccubeConfig;

        if (!isset($eccubeConfig['multi_lingual_text'][$key])) {
            throw new \InvalidArgumentException("multi_lingual_text.{$key} doesn't exist.");
        }
        if (isset($eccubeConfig['multi_lingual_text'][$key][$locale])) {
            return $eccubeConfig['multi_lingual_text'][$key][$locale];
        } else {
            // 該当localeの定義がない場合は最初に定義されているものを返す
            $locales = array_keys($eccubeConfig['multi_lingual_text'][$key]);
            if (count($locales)) {
                return $eccubeConfig['multi_lingual_text'][$key][$locales[0]];
            } else {
                return '';
            }
        }
        // not to reach
    }
}