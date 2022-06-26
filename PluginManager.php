<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Eccube\Common\Constant;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Block;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\Category;
use Eccube\Entity\Layout;
use Eccube\Entity\Master\AbstractMasterEntity;
use Eccube\Entity\Page;
use Eccube\Entity\PageLayout;
use Eccube\Entity\Product;
use Eccube\Entity\Master\DeviceType;
use Eccube\Plugin\AbstractPluginManager;
use Plugin\MultiLingual\Entity\LocaleCategory;
use Plugin\MultiLingual\Entity\LocaleProduct;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class PluginManager extends AbstractPluginManager
{
    public function enable(array $meta, ContainerInterface $container)
    {
        $this->createTemplateSymbolicLink();
        $this->createPageRecord($container);
        $this->copyBlockTemplate($container);
        $this->createLocaleCategory($container);
        $this->createLocaleProduct($container);
        $this->createMasterLocaleRecord($container);
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
        // ページ管理から該当ページの表示を消す
        $this->removePageRecord($container);

        // app/templateにコピーしたテンプレートは残しておく

        // 再度enableした時に翻訳データが残っているようにLocaleレコードは削除しない。
        // enable時は足りないレコードのみ新規作成する。
        //$this->cleanupLocaleRecords($container);

        // シンボリックリンク削除
        $fs = new Filesystem;
        $fs->remove(__DIR__ . '/Resource/template/default');
    }

    /**
     * @param ContainerInterface $container
     * @return EntityManager
     */
    private function getEntityManager(ContainerInterface $container): EntityManager
    {
        /** @var EntityManager $em */
        $em = $container->get('doctrine.orm.entity_manager');
        return $em;
    }

    /**
     * Resource/setup/以下にある設定ファイルを読み込む。
     *
     * @param string $file
     * @return mixed
     */
    private function loadSetupFile(string $file)
    {
        return include(__DIR__ . '/Resource/setup/' . $file);
    }

    /**
     * 指定テーブルをtruncate。
     *
     * @param ContainerInterface $container
     * @param string $table
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    private function truncateTable(ContainerInterface  $container, string $table)
    {
        $em = $this->getEntityManager($container);

        $connection = $em->getConnection();
        $platform = $connection->getDatabasePlatform();
        if (strpos(Constant::VERSION, '4.0.') === 0) {
            // EC-CUBE4.0(Symfony3)
            $connection->executeUpdate($platform->getTruncateTableSQL($table));
        } else {
            // EC-CUBE4.1(Symfony4)
            $connection->executeStatement($platform->getTruncateTableSQL($table));
        }
    }


    /**
     * 指定Entityのテーブルをtruncateする。
     *
     * @param ContainerInterface $container
     * @param string $entity
     * @return void
     */
    private function truncate(ContainerInterface  $container, string $entity)
    {
        $em = $this->getEntityManager($container);
        $tableName = $em->getClassMetadata($entity)->getTableName();
        $this->truncateTable($container, $tableName);
    }

    /**
     * 該当バージョンのテンプレートディレクトリへのシンボリックリンク作成
     *
     * @return void
     */
    private function createTemplateSymbolicLink()
    {
        $version = '4.1';
        if (preg_match('/^(\d\.\d+)/', Constant::VERSION, $matches)) {
            $version = $matches[1];
        }
        $fs = new Filesystem;
        $fs->symlink(
            __DIR__ . '/Resource/template/default' . $version,
            __DIR__ . '/Resource/template/default',
            true
        );
    }

    /**
     * ページ関連のレコードを作成する。
     * - Locale用Page,Layout,Blockの作成。
     * - 新規作成したPageをLayoutに登録する。
     * - 新規作成したLayoutにBlockを登録する。
     *
     * @param ContainerInterface $container
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createPageRecord(ContainerInterface $container)
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
        $layouts = $this->loadSetupFile('layouts.php');
        foreach ($layouts as $l) {
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
        $blocks = $this->loadSetupFile('blocks.php');
        foreach ($blocks as $b) {
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
        foreach ($layouts as $l) {
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

        /** @var Category[] $categories */
        $categories = $em->getRepository(Category::class)->findAll();

        $localeRepository = $em->getRepository(LocaleCategory::class);

        foreach ($categories as $category) {
            foreach ($locales as $locale) {
                $entity = $localeRepository->findOneBy([
                    'parent_id' => $category->getId(),
                    'locale'    => $locale,
                ]);
                if ($entity) {
                    continue;
                }
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
     * plg_locale_productの設定
     *
     * @param ContainerInterface $container
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createLocaleProduct(ContainerInterface  $container)
    {
        $em = $this->getEntityManager($container);

        /** @var EccubeConfig $eccubeConfig */
        $eccubeConfig = $container->get(EccubeConfig::class);
        $locales = $eccubeConfig['multi_lingual_locales'];

        /** @var Product[] $products */
        $products = $em->getRepository(Product::class)->findAll();

        $localeRepository = $em->getRepository(LocaleProduct::class);

        foreach ($products as $product) {
            foreach ($locales as $locale) {
                $entity = $localeRepository->findOneBy([
                    'parent_id' => $product->getId(),
                    'locale'    => $locale,
                ]);
                if ($entity) {
                    continue;
                }
                $lp = new LocaleProduct();
                $lp->setParentId($product->getId());
                $lp->setProduct($product);
                $lp->setName($product->getName());
                $lp->setDescriptionDetail($product->getDescriptionDetail());
                $lp->setDescriptionList($product->getDescriptionList());
                $lp->setLocale($locale);
                $em->persist($lp);
                $em->flush();
            }
        }
    }

    /**
     * MasterテーブルのLocaleデータを設定する。
     *
     * Locale Entityのクラス名は$masterClassのgetLocaleClass()で取得できそうだが、
     * Pluginのenableがまだなので、Traitで拡張するgetLocaleClass()はまだ使えない。
     * このため$localeClass引数で指定する
     *
     * @param ContainerInterface $container
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createMasterLocaleRecord(
        ContainerInterface $container
    )
    {
        $em = $this->getEntityManager($container);

        /** @var EccubeConfig $eccubeConfig */
        $eccubeConfig = $container->get(EccubeConfig::class);
        $locales = $eccubeConfig['multi_lingual_locales'];

        $masters = $this->loadSetupFile('master_locales.php');
        foreach ($masters as $master) {
            $masterClass = $master['entity'];
            $localeClass = $master['locale_entity'];

            /** @var AbstractMasterEntity[] $entities */
            $entities = $em->getRepository($masterClass)->findAll();

            $localeRepository = $em->getRepository($localeClass);

            foreach ($entities as $entity) {
                foreach ($locales as $locale) {
                    $exists = $localeRepository->findOneBy([
                        'parent_id' => $entity->getId(),
                        'locale'    => $locale,
                    ]);
                    if ($exists) {
                        continue;
                    }
                    $LocaleEntity = new $localeClass;
                    $LocaleEntity->setParent($entity);
                    // 翻訳データがあれば登録
                    $name = $entity->getName();
                    if (isset($master['translates'][$name]) &&
                        isset($master['translates'][$name][$locale])) {
                        $LocaleEntity->setName($master['translates'][$name][$locale]);
                    } else {
                        $LocaleEntity->setName($name);
                    }
                    $LocaleEntity->setLocale($locale);
                    $em->persist($LocaleEntity);
                    $em->flush();
                }
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
    private function removePageRecord(ContainerInterface $container)
    {
        $em = $this->getEntityManager($container);

        $layoutRepository = $em->getRepository(Layout::class);
        $pageRepository = $em->getRepository(Page::class);
        $pageLayoutRepository = $em->getRepository(PageLayout::class);
        $blockRepository = $em->getRepository(Block::class);
        $bpRepository = $em->getRepository(BlockPosition::class);

        // Page,PageLayoutを削除
        $layouts = $this->loadSetupFile('layouts.php');
        foreach ($layouts as $l) {
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
        foreach ($layouts as $l) {
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
        foreach ($layouts as $l) {
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
        $blocks = $this->loadSetupFile('blocks.php');
        foreach ($blocks as $b) {
            $Block = $blockRepository->findOneBy(['file_name' => $b['file_name']]);
            if (!$Block) {
                continue;
            }

            $em->remove($Block);
            $em->flush();
        }
    }

    /**
     * Locale関連テーブルのtruncate処理
     *
     * @param ContainerInterface $container
     * @return void
     */
    private function cleanupLocaleRecords(ContainerInterface $container)
    {
        $this->truncate($container, LocaleCategory::class);
        $this->truncate($container, LocaleProduct::class);

        $em = $this->getEntityManager($container);

        $masters = $this->loadSetupFile('master_locales.php');
        foreach ($masters as $master) {
            $localeClass = $master['locale_entity'];
            $tableName = $em->getClassMetadata($localeClass)->getTableName();
            $this->truncateTable($container, $tableName);

        }
    }
}
