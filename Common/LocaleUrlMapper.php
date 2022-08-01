<?php

namespace Plugin\MultiLingual\Common;

use Eccube\Common\EccubeConfig;

class LocaleUrlMapper
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
     * URLを指定localeのものに変換する。
     *
     * @param string $url
     * @param string|null $locale    nullの場合、デフォルト(日本語locale)のURLに変換
     * @return string
     */
    public function map(string $url, ?string $locale): string
    {
        $component = parse_url($url);
        if ($component === false) {
            throw new \InvalidArgumentException('invalid url');
        }

        $path = $component['path'];
        $path = $this->stripLocale($path);

        if ($locale === null) {
            if ($path === '/home') {
                $path = '/';
            }
            $component['path'] = $path;
            return ParseUrlHelper::buildURL($component);
        }

        if ($path === '/') {
            $path = '/home';
        }
        $component['path'] = '/' . $locale . $path;
        return ParseUrlHelper::buildURL($component);
    }

    /**
     * pathからlocaleの階層を削除する。
     * 例: /en/shopping -> /shopping
     * localeの階層がなければ、$pathをそのまま返す。
     *
     * @param string $path
     * @return string
     */
    private function stripLocale(string $path): string
    {
        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            if (strpos($path, '/' . $locale . '/') === 0) {
                $path = substr($path, strlen('/' . $locale));
                break;
            }
        }
        return $path;
    }
}

