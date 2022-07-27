<?php

namespace Plugin\MultiLingual\Twig;

use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Category;
use Plugin\MultiLingual\Common\LocaleHelper;
use Plugin\MultiLingual\Common\LocaleText;
use Plugin\MultiLingual\Common\ParseUrlHelper;
use Plugin\MultiLingual\Entity\LocaleCategory;
use Plugin\MultiLingual\Entity\LocaleClassCategory;
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

    /**
     * @var LocaleText
     */
    private $localeText;

    public function __construct(
        UrlGeneratorInterface $generator,
        EntityManagerInterface  $entityManager,
        LocaleText $localeText
    )
    {
        $this->generator = $generator;
        $this->em = $entityManager;
        $this->localeText = $localeText;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('locale_url', [$this, 'getLocaleUrl']),
            new TwigFunction('locale_path', [$this, 'getLocalePath']),
            new TwigFunction('locale_field', [$this, 'getLocaleField']),
            new TwigFunction('locale_text', [$this, 'getLocaleText']),
            new TwigFunction('insert_locale_into_url', [$this, 'insertLocaleIntoUrl']),
            new TwigFunction('trans_class_categories', [$this, 'translateClassCategoriesJson']),
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
        $locale = LocaleHelper::getCurrentRequestLocale();

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
     * services.yamlに定義されているlocaleごとのテキストを取得する。
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
     * URLにlocale文字列を挿入する。
     *
     * @param string $url
     * @param string|null $locale
     * @return string
     */
    public function insertLocaleIntoUrl(string $url, ?string $locale = null): string
    {
        if ($locale === null) {
            $locale = LocaleHelper::getCurrentRequestLocale();
        }

        $component = parse_url($url);
        if ($component === false) {
            return $url;
        }
        if (isset($component['path'])) {
            if (substr($component['path'], 0, 1) == '/') {
                $component['path'] = '/' . $locale . $component['path'];
            } else {
                $component['path'] = $locale . '/' . $component['path'];
            }
        }

        return ParseUrlHelper::buildURL($component);
    }

    /**
     * trans_class_categories()が作成した文字列の規格カテゴリ名を翻訳する。
     * 規格2の選択プルダウンの言語表示を切り替えるのに使用する。
     *
     * @param string $json  trans_class_categories()が返したjson文字列
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

