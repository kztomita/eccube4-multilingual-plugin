<?php

namespace Plugin\MultiLingual;

use Eccube\Entity\Layout;
use Eccube\Entity\Page;
use Eccube\Entity\PageLayout;
use Eccube\Entity\Master\DeviceType;
use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    public function install(array $meta, ContainerInterface $container)
    {
        $this->createRecord($container);
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
                print("pageid:".$Page->getId()."\n");
                print("layoutid:".$Layout->getId()."\n");
                $PageLayout->setSortNo($sort++);
                $em->persist($PageLayout);
                $em->flush();
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
    }
}
