<?php

namespace Plugin\MultiLingual\Twig;

use Eccube\Entity\Category;
use Plugin\MultiLingual\Entity\LocaleCategory;
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
            new TwigFunction('locale_category_name', [$this, 'getLocaleCategoryName']),
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
     * @param array  $parameters
     * @param bool   $schemeRelative
     *
     * @return string
     */
    public function getLocaleUrl(string $name, array $parameters = [], bool $schemeRelative = false): string
    {
        $locale = $this->getCurrentRequestLocale();

        if (!isset($parameters['_locale'])) {
            $parameters['_locale'] = $locale;
        }

        return $this->generator->generate($name, $parameters, $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL);
    }


    /**
     * 指定Localeでのカテゴリ名を返す。
     * $localeを指定しなかった場合は、現在のリクエストのLocaleを使用する。
     *
     * @param Category $Category
     * @param string|null $locale
     * @return string
     */
    public function getLocaleCategoryName(Category $Category, ?string $locale = null): string
    {
        if ($locale === null) {
            $locale = $this->getCurrentRequestLocale();
        }

        $localeCategoryRepository = $this->em->getRepository(LocaleCategory::class);

        /** @var LocaleCategory $LocaleCategory */
        $LocaleCategory = $localeCategoryRepository->findOneBy([
            'category_id' => $Category->getId(),
            'locale' => $locale,
        ]);
        if (!$LocaleCategory) {
            return $Category->getName();
        }

        return $LocaleCategory->getName();
    }
}

