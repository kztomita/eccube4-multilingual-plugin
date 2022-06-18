<?php

namespace Plugin\MultiLingual\Twig;

use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Category;
use Plugin\MultiLingual\Entity\LocaleCategory;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;

class TwigExtension extends AbstractExtension
{
    /**
     * @var UrlGeneratorInterface
     */
    private $generator;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        UrlGeneratorInterface $generator,
        EntityManagerInterface  $entityManager
    )
    {
        $this->generator = $generator;
        $this->em = $entityManager;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('locale_url', [$this, 'getLocaleUrl']),
            new TwigFunction('locale_path', [$this, 'getLocalePath']),
            new TwigFunction('locale_field', [$this, 'getLocaleField']),
        ];
    }

    private function getCurrentRequestLocale(): string
    {
        if(isset($GLOBALS['request']) && $GLOBALS['request']) {
            $locale = $GLOBALS['request']->getLocale();
        } else {
            $locale = env('ECCUBE_LOCALE', 'ja_JP');
        }

        return $locale;
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
     * @param mixed  $parameters
     * @param bool   $schemeRelative
     *
     * @return string
     */
    public function getLocaleUrl(string $name, $parameters = [], bool $schemeRelative = false): string
    {
        $locale = $this->getCurrentRequestLocale();

        if (!isset($parameters['_locale'])) {
            $parameters['_locale'] = $locale;
        }

        return $this->generator->generate($name, $parameters, $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * 現在のlocaleのpathを生成する。
     * {{ locale_path('product_list_locale') }}
     * は
     * {{ path('product_list_locale', {'_locale': app.request.locale}) }}
     * とするのと同じ。
     *
     * @param string $name
     * @param mixed  $parameters
     * @param bool   $relative
     *
     * @return string
     */
    public function getLocalePath(string $name, $parameters = [], bool $relative = false): string
    {
        $locale = $this->getCurrentRequestLocale();

        if (!isset($parameters['_locale'])) {
            $parameters['_locale'] = $locale;
        }

        return $this->generator->generate($name, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * $Entityの指定LocaleのLocaleオブジェクトを取得し、指定フィールドの値を返す。
     * $localeを指定しなかった場合は、現在のリクエストのLocaleを使用する。
     * 該当するLocaleオブジェクトがない場合は、$Entityの同名フィールドの値を返す。
     *
     * @param AbstractEntity $Entity
     * @param string $field
     * @param string|null $locale
     * @return string
     */
    public function getLocaleField(AbstractEntity $Entity, string $field, ?string $locale = null): string
    {
        if (!method_exists($Entity, 'getLocales')) {
            throw new \InvalidArgumentException('$Entity has no getLocales() method.');
        }

        if ($locale === null) {
            $locale = $this->getCurrentRequestLocale();
        }

        $method = 'get' . Container::camelize($field);

        $localeClass = $Entity->getLocaleClass();
        $localeRepository = $this->em->getRepository($localeClass);

        $criteria = [
            'locale' => $locale,
        ];
        $criteria[$localeClass::getParentColumn()] = $Entity->getId();

        $LocaleEntity = $localeRepository->findOneBy($criteria);
        if (!$LocaleEntity) {
            return $Entity->$method();
        }

        return $LocaleEntity->$method();
    }
}

