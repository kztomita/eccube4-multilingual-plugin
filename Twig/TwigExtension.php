<?php

namespace Plugin\MultiLingual\Twig;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\AbstractEntity;
use phpDocumentor\Reflection\Types\Mixed_;
use Plugin\MultiLingual\Common\LocaleHelper;
use Plugin\MultiLingual\Common\LocaleText;
use Plugin\MultiLingual\Common\LocaleUrlMapper;
use Plugin\MultiLingual\Entity\AbstractLocaleEntity;
use Plugin\MultiLingual\Entity\LocaleClassCategory;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
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

    /**
     * @var LocaleText
     */
    private $localeText;

    /**
     * @var LocaleUrlMapper
     */
    private $localeUrlMapper;

    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    public function __construct(
        UrlGeneratorInterface $generator,
        EntityManagerInterface  $entityManager,
        LocaleText $localeText,
        LocaleUrlMapper $mapper,
        EccubeConfig $eccubeConfig
    )
    {
        $this->generator = $generator;
        $this->em = $entityManager;
        $this->localeText = $localeText;
        $this->localeUrlMapper = $mapper;
        $this->eccubeConfig = $eccubeConfig;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('current_locale', [$this, 'getCurrentLocale']),
            new TwigFunction('locale_config', [$this, 'getLocaleConfig']),
            new TwigFunction('locale_name', [$this, 'getLocaleName']),
            new TwigFunction('locale_url', [$this, 'getLocaleUrl']),
            new TwigFunction('locale_path', [$this, 'getLocalePath']),
            new TwigFunction('locale_field', [$this, 'getLocaleField']),
            new TwigFunction('locale_text', [$this, 'getLocaleText']),
            new TwigFunction('find_locale_entity', [$this, 'findLocaleEntity']),
            new TwigFunction('map_locale_url', [$this, 'mapLocaleUrl']),
            new TwigFunction('trans_class_categories', [$this, 'translateClassCategoriesJson']),
        ];
    }

    /**
     * ???????????????????????????locale????????????
     *
     * @return string
     */
    public function getCurrentLocale(): string
    {
        return LocaleHelper::getCurrentRequestLocale();
    }

    /**
     * services.yaml???????????????locale???multi_lingual_locale????????????????????????
     *
     * @params ?string $locale
     * @return mixed
     */
    public function getLocaleConfig(?string $locale = null)
    {
        if ($locale === null) {
            $locale = LocaleHelper::getCurrentRequestLocale();
        }

        $eccubeConfig = $this->eccubeConfig;

        if (!isset($eccubeConfig['multi_lingual_locale'][$locale])) {
            return null;
        }
        return $eccubeConfig['multi_lingual_locale'][$locale];
    }

    /**
     * services.yaml????????????????????????locale???????????????
     * locale_config(locale).name ???????????????????????????
     *
     * @param string $locale
     * @return string
     */
    public function getLocaleName(string $locale): string
    {
        $eccubeConfig = $this->eccubeConfig;

        if (isset($eccubeConfig['multi_lingual_locale'][$locale])) {
            return $eccubeConfig['multi_lingual_locale'][$locale]['name'];
        }
        if ($locale == 'ja') {
            return '?????????';
        }
        return $locale;
    }

    /**
     * ?????????locale???URL??????????????????
     * {{ locale_url('product_list_locale') }}
     * ???
     * {{ url('product_list_locale', {'_locale': app.request.locale}) }}
     * ????????????????????????
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
        $locale = LocaleHelper::getCurrentRequestLocale();

        if (!isset($parameters['_locale'])) {
            $parameters['_locale'] = $locale;
        }

        return $this->generator->generate($name, $parameters, $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * ?????????locale???path??????????????????
     * {{ locale_path('product_list_locale') }}
     * ???
     * {{ path('product_list_locale', {'_locale': app.request.locale}) }}
     * ????????????????????????
     *
     * @param string $name
     * @param mixed  $parameters
     * @param bool   $relative
     *
     * @return string
     */
    public function getLocalePath(string $name, $parameters = [], bool $relative = false): string
    {
        $locale = LocaleHelper::getCurrentRequestLocale();

        if (!isset($parameters['_locale'])) {
            $parameters['_locale'] = $locale;
        }

        return $this->generator->generate($name, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * $Entity?????????Locale???Locale????????????????????????????????????????????????????????????????????????
     * $locale???????????????????????????????????????????????????????????????Locale??????????????????
     * ????????????Locale???????????????????????????????????????$Entity??????????????????????????????????????????
     *
     * @param ?AbstractEntity $Entity
     * @param string $field
     * @param string|null $locale
     * @return mixed
     */
    public function getLocaleField(?AbstractEntity $Entity, string $field, ?string $locale = null)
    {
        if ($Entity === null) {
            return null;
        }

        if (!LocaleHelper::hasLocaleFeature($Entity)) {
            throw new \InvalidArgumentException('$Entity has no getLocales() method.');
        }

        return LocaleHelper::getLocaleField($Entity, $field, $locale);
    }

    /**
     * services.yaml????????????????????????locale???????????????????????????????????????
     *
     * @param string $key
     * @param string|null $locale
     * @return string
     */
    public function getLocaleText(string $key, ?string $locale = null): string
    {
        return $this->localeText->getText($key, $locale);
    }

    /**
     * Locale???Entity?????????????????????
     *
     * @param string $entityName  Locale Entity???????????????
     * @param int|null|string $parentId      ???Entity???ID
     * @param string|null $locale
     * @return AbstractLocaleEntity|null
     */
    public function findLocaleEntity(string $entityName, $parentId, ?string $locale = null): ?AbstractLocaleEntity
    {
        // ??????????????????
        if ($parentId === null || $parentId === '') {
            return null;
        }

        $locale = $locale ?? LocaleHelper::getCurrentRequestLocale();

        /** @var ?AbstractLocaleEntity $entity */
        $entity = $this->em->getRepository($entityName)->findOneBy([
            'parent_id' => $parentId,
            'locale' => $locale,
        ]);
        return $entity;
    }

    public function mapLocaleUrl(string $url, ?string $locale): string
    {
        return $this->localeUrlMapper->map($url, $locale);
    }

    /**
     * trans_class_categories()??????????????????????????????????????????????????????????????????
     * ??????2??????????????????????????????????????????????????????????????????????????????
     *
     * @param string $json  trans_class_categories()????????????json?????????
     * @param ?string $locale
     * @return string
     */
    public function translateClassCategoriesJson(string $json, ?string $locale = null): string
    {
        $classCategories = json_decode($json, true);
        if ($classCategories === null) {
            return $json;
        }

        $repository = $this->em->getRepository(LocaleClassCategory::class);

        $locale = $locale ?? LocaleHelper::getCurrentRequestLocale();

        $id1keys = array_filter(
            array_keys($classCategories),
            function ($v) {return is_int($v);}
        );

        foreach ($id1keys as $id1key) {
            $id2keys = array_filter(
                array_keys($classCategories[$id1key]),
                function ($v) {return $v != '#';}
            );
            foreach ($id2keys as $id2key) {
                $LocaleClassCategory = $repository->findOneBy([
                    'parent_id' => $classCategories[$id1key][$id2key]['classcategory_id2'],
                    'locale' => $locale,
                ]);
                if ($LocaleClassCategory) {
                    $classCategories[$id1key][$id2key]['name'] = $LocaleClassCategory->getName();
                }
            }
        }

        return json_encode($classCategories);
    }
}

