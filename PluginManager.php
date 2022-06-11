<?php

namespace Plugin\MultiLingual;

use Eccube\Entity\Block;
use Eccube\Entity\Layout;
use Eccube\Entity\Page;
use Eccube\Entity\PageLayout;
use Eccube\Entity\Master\DeviceType;
use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class PluginManager extends AbstractPluginManager
{
    /**
     * @var string[]
     */
    private $layouts = [
        [
            'name' => 'トップページ用レイアウト(Locale)',
            'pages' => [],
        ],
        [
            'name' => '下層ページ用レイアウト(Locale)',
            'pages' => [
                [
                    'name'  => '商品一覧ページ(Locale)',
                    'url'   => 'product_list_locale',
                    'file_name' => '',
                ],
                [
                    'name'  => '商品詳細ページ(Locale)',
                    'url'   => 'product_detail_locale',
                    'file_name' => '',
                ]
            ],
        ],
    ];

    private $blocks = [
        [
            'name'      => 'カート - Locale',
            'file_name' => 'locale_cart',
        ],
        [
            'name'      => 'カテゴリ - Locale',
            'file_name' => 'locale_category',
        ],
        [
            'name'      => 'カテゴリナビ(PC) - Locale',
            'file_name' => 'locale_category_nav_pc',
        ],
        [
            'name'      => 'カテゴリナビ(SP) - Locale',
            'file_name' => 'locale_category_nav_sp',
        ],
        [
            'name'      => '新入荷商品特集 - Locale',
            'file_name' => 'locale_eyecatch',
        ],
        [
            'name'      => 'フッター - Locale',
            'file_name' => 'locale_footer',
        ],
        [
            'name'      => 'ヘッダー(商品検索・ログインナビ・カート) - Locale',
            'file_name' => 'locale_header',
        ],
        [
            'name'      => 'ログインナビ(共通) - Locale',
            'file_name' => 'locale_login',
        ],
        [
            'name'      => 'ログインナビ(SP) - Locale',
            'file_name' => 'locale_login_sp',
        ],
        [
            'name'      => 'ロゴ - Locale',
            'file_name' => 'locale_logo',
        ],
        [
            'name'      => '新着商品 - Locale',
            'file_name' => 'locale_new_item',
        ],
        [
            'name'      => '新着情報 - Locale',
            'file_name' => 'locale_news',
        ],
        [
            'name'      => '商品検索 - Locale',
            'file_name' => 'locale_search_product',
        ],
        [
            'name'      => 'トピック - Locale',
            'file_name' => 'locale_topic',
        ],
        [
            'name'      => 'カレンダー - Locale',
            'file_name' => 'locale_calendar',
        ],
    ];

    public function install(array $meta, ContainerInterface $container)
    {
        $this->createRecord($container);
        $this->copyTemplate($container);
    }

    public function uninstall(array $meta, ContainerInterface $container)
    {
        $this->removeRecord($container);
    }

    private function createRecord(ContainerInterface $container)
    {
        /** @var EntityManager */
        $em = $container->get('doctrine.orm.entity_manager');

        $deviceTypeRepository = $em->getRepository(DeviceType::class);
        $DeviceType = $deviceTypeRepository->find(DeviceType::DEVICE_TYPE_PC);

        $sort = 100;

        // Layout,Page,PageLayoutを追加
        foreach ($this->layouts as $l) {
            $Layout = new Layout;
            $Layout->setDeviceType($DeviceType)
                   ->setName($l['name']);

            $em->persist($Layout);
            $em->flush();

            foreach ($l['pages'] as $pg) {
                $Page = new Page;
                // TODO MasterPage,
                $Page->setName($pg['name'])
                     ->setUrl($pg['url'])
                     ->setFileName($pg['file_name'])
                     ->setEditType(Page::EDIT_TYPE_USER);
                $em->persist($Page);
                $em->flush();

                $PageLayout = new PageLayout;
                $PageLayout->setPageId($Page->getId())
                           ->setLayoutId($Layout->getId())
                           ->setPage($Page)
                           ->setLayout($Layout);
                $PageLayout->setSortNo($sort++);
                $em->persist($PageLayout);
                $em->flush();
            }
        }

        // Blockの追加
        foreach ($this->blocks as $b) {
            $Block = new Block;
            $Block->setDeviceType($DeviceType)
                  ->setName($b['name'])
                  ->setFileName($b['file_name'])
                  ->setDeletable(1);
            $em->persist($Block);
            $em->flush();
        }
    }

    private function copyTemplate(ContainerInterface $container)
    {
        $fs = new Filesystem;

        $templateDir = $container->getParameter('eccube_theme_front_dir');

        foreach ($this->blocks as $b) {
            $src = __DIR__ . '/Resource/template/Block/' . $b['file_name'] . '.twig';
            $dst = $templateDir . '/Block/' . $b['file_name'] . '.twig';
            if (!$fs->exists($dst)) {
                $fs->copy($src, $dst);
            }
        }
   }

    private function removeRecord(ContainerInterface $container)
    {
        /** @var EntityManager */
        $em = $container->get('doctrine.orm.entity_manager');

        $layoutRepository = $em->getRepository(Layout::class);
        $pageRepository = $em->getRepository(Page::class);
        $pageLayoutRepository = $em->getRepository(PageLayout::class);
        $blockRepository = $em->getRepository(Block::class);

        // Page,PageLayoutを削除
        foreach ($this->layouts as $l) {
            foreach ($l['pages'] as $pg) {
                $Page = $pageRepository->findOneBy(['url' => $pg['url']]);
                if ($Page) {
                    $PageLayout = $pageLayoutRepository->findOneBy(['page_id' => $Page->getId()]);
                    if ($PageLayout) {
                        $em->remove($PageLayout);
                        $em->flush();
                    }

                    $em->remove($Page);
                    $em->flush();
                }
            }
        }

        // Layoutを削除
        foreach ($this->layouts as $l) {
            $Layout = $layoutRepository->findOneBy(['name' => $l['name']]);
            if (!$Layout) {
                continue;
            }

            // PageLayoutにlayout_id = $Layout->getId()のレコードがないこと
            $count = $em->createQueryBuilder()
                        ->select('count(pl.page_id)')
                        ->from('Eccube\Entity\PageLayout', 'pl')
                        ->where('pl.layout_id = ?1')
                        ->setParameter(1, $Layout->getId())
                        ->getQuery()
                        ->getSingleScalarResult();
            if ($count) {
                continue;
            }

            $em->remove($Layout);
            $em->flush();
        }

        // Blockを削除
        foreach ($this->blocks as $b) {
            $Block = $blockRepository->findOneBy(['file_name' => $b['file_name']]);
            if (!$Block) {
                continue;
            }

            $em->remove($Block);
            $em->flush();
        }
    }
}
