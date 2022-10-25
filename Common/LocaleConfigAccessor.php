<?php

namespace Plugin\MultiLingual\Common;

use Eccube\Common\EccubeConfig;

class LocaleConfigAccessor
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
     * services.yamlのmulti_lingual_localeを取得する。
     *
     * @param ?string $locale  locale。null時は現在のlocale。
     * @return array|null
     */
    public function get(?string $locale = null): ?array
    {
        if ($locale === null) {
            $locale = LocaleHelper::getCurrentRequestLocale();
        }

        return $this->eccubeConfig['multi_lingual_locale'][$locale] ?? null;
    }
}


