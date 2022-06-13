<?php

namespace Plugin\MultiLingual;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Block;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\Category;
use Eccube\Entity\Layout;
use Eccube\Entity\Page;
use Eccube\Entity\PageLayout;
use Eccube\Entity\Master\DeviceType;
use Eccube\Plugin\AbstractPluginManager;
use Plugin\MultiLingual\Entity\LocaleCategory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class PluginManager extends AbstractPluginManager
{
    /**
     * @var array
     */
    private $layouts = [
        [
            'name' => 'トップページ用レイアウト - Locale',
            'pages' => [
                [
                    'name'  => 'TOPページ - Locale',
                    'url'   => 'homepage_locale',
                    'file_name' => 'index',
                    'edit_type' => Page::EDIT_TYPE_DEFAULT,
                ],
            ],
            'src_name' => 'トップページ用レイアウト',
        ],
        [
            'name' => '下層ページ用レイアウト - Locale',
            'pages' => [
                [
                    'name'  => '商品一覧ページ - Locale',
                    'url'   => 'product_list_locale',
                    'file_name' => 'Product/list',
                    'edit_type' => Page::EDIT_TYPE_DEFAULT,
                ],
                [
                    'name'  => '商品詳細ページ - Locale',
                    'url'   => 'product_detail_locale',
                    'file_name' => 'Product/detail',
                    'edit_type' => Page::EDIT_TYPE_DEFAULT,
                ]
            ],
            'src_name' => '下層ページ用レイアウト',
        ],
    ];

    /**
     * @var array
     */
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

    public function enable(array $meta, ContainerInterface $container)
    {
        $this->createRecord($container);
        $this->copyBlockTemplate($container);
        $this->createLocaleCategory($container);
    }

    /**
     * Locale用に作成したPage,Layout,Blockを削除する。
     *
     * app/templateにコピーしたBlockのテンプレートは残しておく。
     * また、ページ管理からテンプレートを編集していた場合、
     * app/template/default/MultiLingual/Resource/template/default
     * にファイルが作成されている作成されたテンプレートファイルも残しておく。
     */
    public function disable(array $meta, ContainerInterface $container)
    {
        $this->removeRecord($container);
        // app/templateにコピーしたテンプレートは残しておく
        $this->truncateLocaleCategory($container);
    }

    /**
     * @param ContainerInterface $container
     * @return EntityManager
     */
    private function getEntityManager(ContainerInterface $container): EntityManager
    {
        return $container->get('doctrine.orm.entity_manager');
    }

    /**
     * 必要なレコードを作成する。
     * - Locale用Page,Layout,Blockの作成。
     * - 新規作成したPageをLayoutに登録する。
     * - 新規作成したLayoutにBlockを登録する。
     *
     * @param ContainerInterface $container
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createRecord(ContainerInterface $container)
    {
        $em = $this->getEntityManager($container);

        $deviceTypeRepository = $em->getRepository(DeviceType::class);

        /** @var DeviceType $DeviceType */
        $DeviceType = $deviceTypeRepository->find(DeviceType::DEVICE_TYPE_PC);
        if (!$DeviceType) {
            throw new \RuntimeException('DeviceType::DEVICE_TYPE_PC not found.');
        }

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
                     ->setFileName('MultiLingual/Resource/template/default/' . $pg['file_name'])
                     ->setEditType($pg['edit_type']);
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
            /** @var Layout $src */
            $src = $layoutRepository->findOneBy(['name' => $l['src_name']]);
            /** @var Layout $dst */
            $dst = $layoutRepository->findOneBy(['name' => $l['name']]);
            if (!$src || !$dst) {
                continue;
            }
            $this->copyLayout($container, $src, $dst);
        }
    }

    /**
     * @param ContainerInterface $container
     * @param Layout $src
     * @param Layout $dst
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function copyLayout(ContainerInterface $container, Layout $src, Layout $dst)
    {
        $em = $this->getEntityManager($container);

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
            /** @var Block $LocaleBlock */

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

    /**
     * テンプレートファイルをapp/templateにコピーする。
     * コピー先にファイルがある場合は上書きしない。
     *
     * @param ContainerInterface $container
     * @return void
     */
    private function copyBlockTemplate(ContainerInterface $container)
    {
        $fs = new Filesystem;

        $templateDir = $container->getParameter('eccube_theme_front_dir');

        $finder = new Finder;
        $finder->files()
            ->in(__DIR__ . '/Resource/template/default/Block/')
            ->name('*.twig');
        foreach ($finder as $file) {
            $dst = $templateDir . '/Block/' . $file->getFilename();
            if (!$fs->exists($dst)) {
                $fs->copy($file->getRealPath(), $dst);
            }
        }
    }

    /**
     * plg_locale_categoryの設定
     *
     * @param ContainerInterface $container
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createLocaleCategory(ContainerInterface  $container)
    {
        $em = $this->getEntityManager($container);

        /** @var EccubeConfig $eccubeConfig */
        $eccubeConfig = $container->get(EccubeConfig::class);
        $locales = $eccubeConfig['multi_lingual_locales'];

        $categoryRepository = $em->getRepository(Category::class);
        /** @var Category[] $categories */
        $categories = $categoryRepository->findAll();

        foreach ($categories as $category) {
            foreach ($locales as $locale) {
                $lc = new LocaleCategory();
                $lc->setCategory($category);
                $lc->setName($category->getName());
                $lc->setLocale($locale);
                $em->persist($lc);
                $em->flush();
            }
        }
    }

    /**
     * @param ContainerInterface $container
     * @return void
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function removeRecord(ContainerInterface $container)
    {
        $em = $this->getEntityManager($container);

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

    /**
     * @param ContainerInterface $container
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    private function truncateLocaleCategory(ContainerInterface  $container)
    {
        $em = $this->getEntityManager($container);

        $connection = $em->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeStatement($platform->getTruncateTableSQL('plg_locale_category'));
    }
}
