<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace Plugin\MultiLingual;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Eccube\Common\Constant;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Block;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\Category;
use Eccube\Entity\ClassCategory;
use Eccube\Entity\ClassName;
use Eccube\Entity\Csv;
use Eccube\Entity\Delivery;
use Eccube\Entity\DeliveryTime;
use Eccube\Entity\Layout;
use Eccube\Entity\Master\AbstractMasterEntity;
use Eccube\Entity\Master\CsvType;
use Eccube\Entity\News;
use Eccube\Entity\Order;
use Eccube\Entity\Page;
use Eccube\Entity\PageLayout;
use Eccube\Entity\Payment;
use Eccube\Entity\Product;
use Eccube\Entity\Master\DeviceType;
use Eccube\Entity\Shipping;
use Eccube\Entity\Tag;
use Eccube\Plugin\AbstractPluginManager;
use Plugin\MultiLingual\Entity\LocaleCategory;
use Plugin\MultiLingual\Entity\LocaleClassCategory;
use Plugin\MultiLingual\Entity\LocaleClassName;
use Plugin\MultiLingual\Entity\LocaleDelivery;
use Plugin\MultiLingual\Entity\LocaleDeliveryTime;
use Plugin\MultiLingual\Entity\LocalePayment;
use Plugin\MultiLingual\Entity\LocaleProduct;
use Plugin\MultiLingual\Entity\LocaleTag;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class PluginManager extends AbstractPluginManager
{
    public function enable(array $meta, ContainerInterface $container)
    {
        $this->createTemplateSymbolicLink();
        $this->createPageRecord($container);
        $this->copyTemplate($container);
        $this->createLocaleCategory($container);
        $this->createLocaleProduct($container);
        $this->createLocaleClassName($container);
        $this->createLocaleClassCategory($container);
        $this->createLocaleTag($container);
        $this->createLocaleDelivery($container);
        $this->createLocaleDeliveryTime($container);
        $this->createLocalePayment($container);
        $this->createMasterLocaleRecord($container);
        $this->createCsvRecord($container);
        $this->setNewsLocale($container);
    }

    /**
     * Locale??????????????????Page,Layout,Block??????????????????
     *
     * app/template??????????????????Block??????????????????????????????????????????
     * ??????????????????????????????????????????????????????????????????????????????
     * app/template/default/MultiLingual/Resource/template/default
     * ?????????????????????????????????????????????????????????????????????????????????????????????????????????
     */
    public function disable(array $meta, ContainerInterface $container)
    {
        // ??????????????????????????????????????????????????????
        $this->removePageRecord($container);

        $this->cleanupTemplate($container);

        // ??????enable??????????????????????????????????????????????????????Locale?????????????????????????????????
        // enable?????????????????????????????????????????????????????????
        //$this->cleanupLocaleRecords($container);

        $this->cleanupCsvRecord($container);

        // ?????????????????????????????????
        $fs = new Filesystem;
        $fs->remove(__DIR__ . '/Resource/template/default');
        $fs->remove(__DIR__ . '/Resource/template/admin');
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
     * Resource/setup/???????????????????????????????????????????????????
     *
     * @param string $file
     * @return mixed
     */
    private function loadSetupFile(string $file)
    {
        return include(__DIR__ . '/Resource/setup/' . $file);
    }

    private function executeStatement(
        Connection $connection,
        string $query,
        array $params = [],
        array $types = []
    ): int
    {
        if (strpos(Constant::VERSION, '4.0.') === 0) {
            // EC-CUBE4.0(Symfony3)
            return $connection->executeUpdate($query, $params, $types);
        } else {
            // EC-CUBE4.1(Symfony4)
            return $connection->executeStatement($query, $params, $types);
        }
    }

    /**
     * ?????????????????????truncate???
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
        $this->executeStatement($connection, $platform->getTruncateTableSQL($table));
    }


    /**
     * ??????Entity??????????????????truncate?????????
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
     * ???????????????????????????????????????????????????????????????????????????????????????????????????
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
        $fs->symlink(
            __DIR__ . '/Resource/template/admin' . $version,
            __DIR__ . '/Resource/template/admin',
            true
        );
    }

    /**
     * ????????????????????????????????????????????????
     * - Locale???Page,Layout,Block????????????
     * - ??????????????????Page???Layout??????????????????
     * - ??????????????????Layout???Block??????????????????
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

        $masterPages = [];

        // Layout,Page,PageLayout?????????
        $layouts = $this->loadSetupFile('layouts.php');
        foreach ($layouts as $l) {
            $Layout = new Layout;
            $Layout->setDeviceType($DeviceType)
                ->setName($l['name']);

            $em->persist($Layout);
            $em->flush();

            foreach ($l['pages'] as $pg) {
                $Page = new Page;
                $Page
                    ->setName($pg['name'])
                    ->setUrl($pg['url'])
                    ->setFileName('MultiLingual/Resource/template/default/' . $pg['file_name'])
                    ->setEditType($pg['edit_type']);
                $em->persist($Page);
                $em->flush();

                $PageLayout = new PageLayout;
                $PageLayout
                    ->setPageId($Page->getId())
                    ->setLayoutId($Layout->getId())
                    ->setPage($Page)
                    ->setLayout($Layout);
                $PageLayout->setSortNo($sort++);
                $em->persist($PageLayout);
                $em->flush();

                if (isset($pg['master_page']) && $pg['master_page'] !== '') {
                    $masterPages[$Page->getId()] = $pg['master_page'];
                }
            }
        }

        // master_page_id?????????
        $pageRepository = $em->getRepository(Page::class);
        foreach ($masterPages as $pageId => $url) {
            $Page = $pageRepository->find($pageId);
            if (!$Page) {
                throw new \LogicException("Page(ID:$pageId) not found.");
            }
            $MasterPage = $pageRepository->findOneBy([
                'url' => $url,
            ]);
            if (!$MasterPage) {
                throw new \LogicException("$url master page not found.");
            }
            $Page->setMasterPage($MasterPage);
            $em->persist($Page);
            $em->flush();
        }

        // Block?????????
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

        // BlockPosition?????????
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
            // ?????????Block??????????????????????????????
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
     * ?????????????????????????????????app/template?????????????????????
     * ??????????????????????????????????????????????????????????????????
     *
     * @param ContainerInterface $container
     * @return void
     */
    private function copyTemplate(ContainerInterface $container)
    {
        $fs = new Filesystem;

        $templateDir = $container->getParameter('eccube_theme_front_dir');

        $dirs = [
            [
                'src' => __DIR__ . '/Resource/template/default/Block/',
                'dst' => $templateDir . '/Block/',
            ],
            [
                'src' => __DIR__ . '/Resource/template/default/Form/',
                'dst' => $templateDir . '/Form/',
            ],
        ];

        foreach ($dirs as $dir) {
            $finder = new Finder;
            $finder->files()
                ->in($dir['src'])
                ->name('*.twig');
            foreach ($finder as $file) {
                $dst = $dir['dst'] . $file->getFilename();
                if (!$fs->exists($dst)) {
                    $fs->copy($file->getRealPath(), $dst);
                }
            }
        }
    }

    /**
     * plg_ml_locale_xxxx?????????????????????????????????????????????????????????
     *
     * @param ContainerInterface $container
     * @param string $class        ??????Entity???????????????
     * @param string $localeClass  Locale????????????
     * @param callable $callback   ?????????????????????Locale Entity???????????????callback
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createLocaleRecord(
        ContainerInterface  $container,
        string $class,
        string $localeClass,
        callable $callback
    )
    {
        $em = $this->getEntityManager($container);

        /** @var EccubeConfig $eccubeConfig */
        $eccubeConfig = $container->get(EccubeConfig::class);
        $locales = $eccubeConfig['multi_lingual_locales'];

        $entities = $em->getRepository($class)->findAll();

        $localeRepository = $em->getRepository($localeClass);

        foreach ($entities as $entity) {
            foreach ($locales as $locale) {
                // ????????????????????????????????????????????????
                $e = $localeRepository->findOneBy([
                    'parent_id' => $entity->getId(),
                    'locale'    => $locale,
                ]);
                if ($e) {
                    continue;
                }

                $localeEntity = new $localeClass();
                $localeEntity->setParentId($entity->getId());
                $localeEntity->setLocale($locale);

                $callback($localeEntity, $entity, $locale);

                $em->persist($localeEntity);
                $em->flush();
            }
        }

    }

    /**
     * plg_ml_locale_category?????????
     *
     * @param ContainerInterface $container
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createLocaleCategory(ContainerInterface  $container)
    {
        $translates = $this->loadSetupFile('initial_data.php')['category']['translates'];

        $this->createLocaleRecord(
            $container,
            Category::class,
            LocaleCategory::class,
            function (LocaleCategory $localeEntity, Category $entity, $locale) use ($translates) {
                $localeEntity->setCategory($entity);

                // ?????????????????????????????????
                $name = $entity->getName();
                if (isset($translates[$name]) &&
                    isset($translates[$name][$locale])) {
                    $localeEntity->setName($translates[$name][$locale]);
                } else {
                    $localeEntity->setName($name);
                }
            }
        );
    }

    /**
     * plg_ml_locale_product?????????
     *
     * @param ContainerInterface $container
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createLocaleProduct(ContainerInterface  $container)
    {
        $translates = $this->loadSetupFile('initial_data.php')['product']['translates'];

        $this->createLocaleRecord(
            $container,
            Product::class,
            LocaleProduct::class,
            function (LocaleProduct $localeEntity, Product $entity, $locale) use ($translates) {
                $localeEntity->setProduct($entity);

                // ?????????????????????????????????
                $name = $entity->getName();
                if (isset($translates[$name]) &&
                    isset($translates[$name][$locale])) {
                    $localeEntity->setName($translates[$name][$locale]);
                } else {
                    $localeEntity->setName($name);
                }

                $localeEntity->setDescriptionDetail($entity->getDescriptionDetail());
                $localeEntity->setDescriptionList($entity->getDescriptionList());
            }
        );
    }

    /**
     * plg_ml_locale_class_name?????????
     *
     * @param ContainerInterface $container
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createLocaleClassName(ContainerInterface  $container)
    {
        $translates = $this->loadSetupFile('initial_data.php')['class_name']['translates'];

        $this->createLocaleRecord(
            $container,
            ClassName::class,
            LocaleClassName::class,
            function (LocaleClassName $localeEntity, ClassName $entity, $locale) use ($translates) {
                $localeEntity->setClassName($entity);

                // ?????????????????????????????????
                $name = $entity->getName();
                if (isset($translates[$name]) &&
                    isset($translates[$name][$locale])) {
                    $localeEntity->setName($translates[$name][$locale]);
                } else {
                    $localeEntity->setName($name);
                }
            }
        );
    }

    /**
     * plg_ml_locale_class_category?????????
     *
     * @param ContainerInterface $container
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createLocaleClassCategory(ContainerInterface  $container)
    {
        $translates = $this->loadSetupFile('initial_data.php')['class_category']['translates'];

        $this->createLocaleRecord(
            $container,
            ClassCategory::class,
            LocaleClassCategory::class,
            function (LocaleClassCategory $localeEntity, ClassCategory $entity, $locale) use ($translates) {
                $localeEntity->setClassCategory($entity);

                // ?????????????????????????????????
                $name = $entity->getName();
                if (isset($translates[$name]) &&
                    isset($translates[$name][$locale])) {
                    $localeEntity->setName($translates[$name][$locale]);
                } else {
                    $localeEntity->setName($name);
                }
            }
        );
    }

    /**
     * plg_ml_locale_tag?????????
     *
     * @param ContainerInterface $container
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createLocaleTag(ContainerInterface  $container)
    {
        $translates = $this->loadSetupFile('initial_data.php')['tag']['translates'];

        $this->createLocaleRecord(
            $container,
            Tag::class,
            LocaleTag::class,
            function (LocaleTag $localeEntity, Tag $entity, $locale) use ($translates) {
                $localeEntity->setTag($entity);

                // ?????????????????????????????????
                $name = $entity->getName();
                if (isset($translates[$name]) &&
                    isset($translates[$name][$locale])) {
                    $localeEntity->setName($translates[$name][$locale]);
                } else {
                    $localeEntity->setName($name);
                }
            }
        );
    }

    /**
     * plg_ml_locale_delivery?????????
     *
     * @param ContainerInterface $container
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createLocaleDelivery(ContainerInterface  $container)
    {
        $translates = $this->loadSetupFile('initial_data.php')['delivery']['translates'];

        $this->createLocaleRecord(
            $container,
            Delivery::class,
            LocaleDelivery::class,
            function (LocaleDelivery $localeEntity, Delivery $entity, $locale) use ($translates) {
                $localeEntity->setDelivery($entity);

                // ?????????????????????????????????
                $name = $entity->getName();
                if (isset($translates[$name]) &&
                    isset($translates[$name][$locale])) {
                    $localeEntity->setName($translates[$name][$locale]);
                } else {
                    $localeEntity->setName($name);
                }

                $serviceName = $entity->getServiceName();
                if (isset($translates[$serviceName]) &&
                    isset($translates[$serviceName][$locale])) {
                    $localeEntity->setServiceName($translates[$serviceName][$locale]);
                } else {
                    $localeEntity->setServiceName($serviceName);
                }
            }
        );

    }

    /**
     * plg_ml_locale_delivery_time?????????
     *
     * @param ContainerInterface $container
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createLocaleDeliveryTime(ContainerInterface  $container)
    {
        $translates = $this->loadSetupFile('initial_data.php')['delivery_time']['translates'];

        $this->createLocaleRecord(
            $container,
            DeliveryTime::class,
            LocaleDeliveryTime::class,
            function (LocaleDeliveryTime $localeEntity, DeliveryTime $entity, $locale) use ($translates) {
                $localeEntity->setParentDeliveryTime($entity);

                // ?????????????????????????????????
                $name = $entity->getDeliveryTime();
                if (isset($translates[$name]) &&
                    isset($translates[$name][$locale])) {
                    $localeEntity->setDeliveryTime($translates[$name][$locale]);
                } else {
                    $localeEntity->setDeliveryTime($name);
                }
            }
        );
    }

    /**
     * plg_ml_locale_payment?????????
     *
     * @param ContainerInterface $container
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createLocalePayment(ContainerInterface  $container)
    {
        $translates = $this->loadSetupFile('initial_data.php')['payment']['translates'];

        $this->createLocaleRecord(
            $container,
            Payment::class,
            LocalePayment::class,
            function (LocalePayment $localeEntity, Payment $entity, $locale) use ($translates) {
                $localeEntity->setPayment($entity);

                // ?????????????????????????????????
                $name = $entity->getMethod();
                if (isset($translates[$name]) &&
                    isset($translates[$name][$locale])) {
                    $localeEntity->setMethod($translates[$name][$locale]);
                } else {
                    $localeEntity->setMethod($name);
                }
            }
        );
    }

    /**
     * Master???????????????Locale???????????????????????????
     *
     * Locale Entity??????????????????$masterClass???getLocaleClass()??????????????????????????????
     * Plugin???enable?????????????????????Trait???????????????getLocaleClass()????????????????????????
     * ????????????$localeClass?????????????????????
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
                    // ?????????????????????????????????
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
     * CSV????????????????????????????????????????????????
     *
     * @param ContainerInterface $container
     * @return array
     */
    private function getCsvRecords(ContainerInterface  $container): array
    {
        $records = [];

        /** @var EccubeConfig $eccubeConfig */
        $eccubeConfig = $container->get(EccubeConfig::class);
        $locales = $eccubeConfig['multi_lingual_locales'];

        foreach ($locales as $locale) {
            // field?????????locale??????????????????????????????CSV??????????????????????????????????????????
            // locale?????????????????????????????????
            $records[] = [
                'type' => CsvType::CSV_TYPE_PRODUCT,
                'entity' => addslashes(LocaleProduct::class),
                'field' => 'name_' . $locale,
                'reference_field_name' => null,
                'disp_name' => "?????????({$locale})",
            ];
            $records[] = [
                'type' => CsvType::CSV_TYPE_PRODUCT,
                'entity' => addslashes(LocaleProduct::class),
                'field' => 'description_list_' . $locale,
                'reference_field_name' => null,
                'disp_name' => "????????????(??????)({$locale})",
            ];
            $records[] = [
                'type' => CsvType::CSV_TYPE_PRODUCT,
                'entity' => addslashes(LocaleProduct::class),
                'field' => 'description_detail_' . $locale,
                'reference_field_name' => null,
                'disp_name' => "????????????(??????)({$locale})",
            ];
            $records[] = [
                'type' => CsvType::CSV_TYPE_PRODUCT,
                'entity' => addslashes(LocaleProduct::class),
                'field' => 'free_area_' . $locale,
                'reference_field_name' => null,
                'disp_name' => "??????????????????({$locale})",
            ];

            $records[] = [
                'type' => CsvType::CSV_TYPE_CATEGORY,
                'entity' => addslashes(LocaleCategory::class),
                'field' => 'name_' . $locale,
                'reference_field_name' => null,
                'disp_name' => "???????????????({$locale})",
            ];
        }

        $records[] = [
            'type' => CsvType::CSV_TYPE_ORDER,
            'entity' => addslashes(Order::class),
            'field' => 'locale_payment_method',
            'reference_field_name' => null,
            'disp_name' => "????????????(Locale??????)",
        ];

        $records[] = [
            'type' => CsvType::CSV_TYPE_SHIPPING,
            'entity' => addslashes(Shipping::class),
            'field' => 'locale_delivery_name',
            'reference_field_name' => null,
            'disp_name' => "????????????(Locale??????)",
        ];
        $records[] = [
            'type' => CsvType::CSV_TYPE_SHIPPING,
            'entity' => addslashes(Shipping::class),
            'field' => 'locale_delivery_time',
            'reference_field_name' => null,
            'disp_name' => "???????????????(Locale??????)",
        ];

        return $records;
    }

    /**
     * dtb_csv???export??????????????????????????????
     *
     * @param ContainerInterface $container
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createCsvRecord(ContainerInterface  $container)
    {
        $em = $this->getEntityManager($container);

        $csvRepository = $em->getRepository(Csv::class);
        $csvTypeRepository = $em->getRepository(CsvType::class);

        $records = $this->getCsvRecords($container);

        $nextSortNo = [];

        foreach ($records as $record) {
            $CsvColumn = $csvRepository->findOneBy([
                'CsvType' => $record['type'],
                'entity_name' => $record['entity'],
                'field_name' => $record['field'],
            ]);
            if (!$CsvColumn) {
                if (!isset($nextSortNo[$record['type']])) {
                    $last = $csvRepository->findOneBy(
                        ['CsvType' => $record['type']],
                        ['sort_no' => 'DESC']
                    );
                    $nextSortNo[$record['type']] = $last ? $last->getSortNo() + 1 : 1;
                }

                /** @var ?CsvType $CsvType */
                $CsvType = $csvTypeRepository->find($record['type']);

                $CsvColumn = new Csv();
                $CsvColumn
                    ->setCsvType($CsvType)
                    ->setEntityName($record['entity'])
                    ->setFieldName($record['field'])
                    ->setReferenceFieldName($record['reference_field_name'])
                    ->setDispName($record['disp_name'])
                    ->setSortNo($nextSortNo[$record['type']]++)
                    ->setEnabled(true);
                $em->persist($CsvColumn);
                $em->flush();
            }
        }
    }

    /**
     * dtb_news???locale????????????????????????
     *
     * @param ContainerInterface $container
     * @return void
     */
    private function setNewsLocale(ContainerInterface $container)
    {
        $em = $this->getEntityManager($container);

        // Plugin???enable?????????????????????Trait???????????????get/setLocale()????????????????????????
        // SQL????????????????????????????????????

        $connection = $em->getConnection();
        $this->executeStatement(
            $connection,
            "UPDATE dtb_news SET locale = ? WHERE locale = ''",
            [env('ECCUBE_LOCALE', 'ja')]
        );
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

        // Page,PageLayout?????????
        $layouts = $this->loadSetupFile('layouts.php');
        // ???????????????????????????????????????????????????master_page_id??????????????????
        foreach ($layouts as $l) {
            foreach ($l['pages'] as $pg) {
                /** @var ?Page $Page */
                $Page = $pageRepository->findOneBy(['url' => $pg['url']]);
                if ($Page && $Page->getMasterPage()) {
                    $Page->setMasterPage(null);
                    $em->remove($Page);
                    $em->flush();
                }
            }
        }
        // ?????????????????????
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

        // ????????????Layout?????????BlockPosition?????????
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

        // Layout?????????
        foreach ($layouts as $l) {
            $Layout = $layoutRepository->findOneBy(['name' => $l['name']]);
            if (!$Layout) {
                continue;
            }

            // PageLayout???layout_id = $Layout->getId()??????????????????????????????
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

        // Block?????????
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
     * ?????????????????????????????????????????????
     *
     * @param ContainerInterface $container
     * @return void
     */
    private function cleanupTemplate(ContainerInterface $container)
    {
        $fs = new Filesystem;

        $templateDir = $container->getParameter('eccube_theme_front_dir');

        // ??????????????????????????????????????????????????????????????????????????????
        $files = [
            $templateDir . '/Block/header.twig',
            $templateDir . '/Block/news.twig',
        ];

        foreach ($files as $file) {
            $fs->rename($file, $file . '.bak', true);
        }
    }

    /**
     * Locale?????????????????????truncate??????
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

    /**
     * dtb_csv??????????????????????????????cleanup
     *
     * @param ContainerInterface $container
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function cleanupCsvRecord(ContainerInterface  $container)
    {
        $em = $this->getEntityManager($container);

        $csvRepository = $em->getRepository(Csv::class);

        $records = $this->getCsvRecords($container);

        foreach ($records as $record) {
            $CsvColumn = $csvRepository->findOneBy([
                'CsvType' => $record['type'],
                'entity_name' => $record['entity'],
                'field_name' => $record['field'],
            ]);
            if ($CsvColumn) {
                $em->remove($CsvColumn);
            }
        }
        $em->flush();
    }
}
