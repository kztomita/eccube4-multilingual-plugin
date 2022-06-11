<?php

namespace Plugin\MultiLingual\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TwigExtension extends AbstractExtension
{
    private $generator;

    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('locale_url', [$this, 'getLocaleUrl']),
        ];
    }

    /**
     * 現在のlocaleのURLを生成する。
     * {{ locale_url('product_list_locale') }}
     * は
     * {{ url('product_list_locale', {'_locale': app.request.locale}) }}
     * とするのと同じ。
     *
     * Ref. to Symfony\Bridge\Twig\Extension\RoutingExtension
     *
     * @param string $name
     * @param array  $parameters
     * @param bool   $schemeRelative
     *
     * @return string
     */
    public function getLocaleUrl($name, $parameters = [], $relative = false)
    {
        $locale = 'en';
        if(isset($GLOBALS['request']) && $GLOBALS['request']) {
            $locale = $GLOBALS['request']->getLocale();
        }

        if (!isset($parameters['_locale'])) {
            $parameters['_locale'] = $locale;
        }

        return $this->generator->generate($name, $parameters, $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

