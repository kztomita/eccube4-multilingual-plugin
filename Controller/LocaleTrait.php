<?php

namespace Plugin\MultiLingual\Controller;

use Symfony\Component\HttpFoundation\Request;

trait LocaleTrait
{
    /**
     * Annotationでrequirements={"_locale": "en|cn"}のように静的に
     * localeを制限するのではなく、parameterで指定できるようにする。
     *
     * @param Request $request
     */
    public function testLocale(Request $request)
    {
        $locales = $this->eccubeConfig['multi_lingual_locales'];

        if (!in_array($request->getLocale(), $locales)) {
            throw $this->createNotFoundException('Unsupported locale.');
        }
   }
}
