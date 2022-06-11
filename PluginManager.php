<?php

namespace Plugin\MultiLingual;

use Eccube\Entity\Block;
use Eccube\Entity\BlockPosition;
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
            'src_name' => 'トップページ用レイアウト',
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
            'src_name' => '下層ページ用レイアウト',
        ],
    ];

    private $blocks = [
        [
            'name'      => 'カート - Locale',
            'file_name' => 'locale_cart',
            'use_controller' => 0,
        ],
        [
            'name'      => 'カテゴリ - Locale',
            'file_name' => 'locale_category',
            'use_controller' => 0,
        ],
        [
            'name'      => 'カテゴリナビ(PC) - Locale',
            'file_name' => 'locale_category_nav_pc',
            'use_controller' => 0,
        ],
        [
            'name'      => 'カテゴリナビ(SP) - Locale',
            'file_name' => 'locale_category_nav_sp',
            'use_controller' => 0,
        ],
        [
            'name'      => '新入荷商品特集 - Locale',
            'file_name' => 'locale_eyecatch',
            'use_controller' => 0,
        ],
        [
            'name'      => 'フッター - Locale',
            'file_name' => 'locale_footer',
            'use_controller' => 0,
        ],
        [
            'name'      => 'ヘッダー(商品検索・ログインナビ・カート) - Locale',
            'file_name' => 'locale_header',
            'use_controller' => 0,
        ],
        [
            'name'      => 'ログインナビ(共通) - Locale',
            'file_name' => 'locale_login',
            'use_controller' => 0,
        ],
        [
            'name'      => 'ログインナビ(SP) - Locale',
            'file_name' => 'locale_login_sp',
            'use_controller' => 0,
        ],
        [
            'name'      => 'ロゴ - Locale',
            'file_name' => 'locale_logo',
            'use_controller' => 0,
        ],
        [
            'name'      => '新着商品 - Locale',
            'file_name' => 'locale_new_item',
            'use_controller' => 0,
        ],
        [
            'name'      => '新着情報 - Locale',
            'file_name' => 'locale_news',
            'use_controller' => 0,
        ],
        [
            'name'      => '商品検索 - Locale',
            'file_name' => 'locale_search_product',
            'use_controller' => 1,
        ],
        [
            'name'      => 'トピック - Locale',
            'file_name' => 'locale_topic',
            'use_controller' => 0,
        ],
        [
            'name'      => 'カレンダー - Locale',
            'file_name' => 'locale_calendar',
            'use_controller' => 1,
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

        $layoutRepository = $em->getRepository(Layout::class);

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
                  ->setUseController($b['use_controller'])
                  ->setDeletable(1);
            $em->persist($Block);
            $em->flush();
        }

        // BlockPositionの設定
        foreach ($this->layouts as $l) {
            $src = $layoutRepository->findOneBy(['name' => $l['src_name']]);
            $dst = $layoutRepository->findOneBy(['name' => $l['name']]);
            if (!$src || !$dst) {
                continue;
            }
            $this->copyLayout($container, $src, $dst);
        }
    }

    private function copyLayout(ContainerInterface $container, Layout $src, Layout $dst)
    {
        /** @var EntityManager */
        $em = $container->get('doctrine.orm.entity_manager');

        $bpRepository = $em->getRepository(BlockPosition::class);
        $blockRepository = $em->getRepository(Block::class);

        $positions = $bpRepository->findBy(['layout_id' => $src->getId()]);
        foreach ($positions as $p) {
            // 同名のBlockと同じ位置に挿入する
            $Block = $blockRepository->find($p->getBlockId());
            if (!$Block) {
                continue;
            }
            $LocaleBlock = $blockRepository->findOneBy(['name' => $Block->getName() . ' - Locale']);
            if (!$LocaleBlock) {
                continue;
            }

            $bp = new BlockPosition;
            $bp->setSection($p->getSection())
               ->setBlockId($LocaleBlock->getId())
               ->setBlock($LocaleBlock)
               ->setLayoutId($dst->getId())
               ->setLayout($dst)
               ->setBlockRow($p->getBlockRow());
            $em->persist($bp);
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
        $bpRepository = $em->getRepository(BlockPosition::class);

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

        // 削除対象Layout配下のBlockPositionを削除
        foreach ($this->layouts as $l) {
            $Layout = $layoutRepository->findOneBy(['name' => $l['name']]);
            if (!$Layout) {
                continue;
            }

            $positions = $bpRepository->findBy(['layout_id' => $Layout->getId()]);
            foreach ($positions as $p) {
                $em->remove($p);
                $em->flush();
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
