<?php

namespace Plugin\MultiLingual;

use Eccube\Entity\Layout;
use Eccube\Entity\Master\DeviceType;
use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PluginManager extends AbstractPluginManager
{
    /**
     * @var string[]
     */
    private $layouts = [
        'トップページ用レイアウト(Locale)',
        '下層ページ用レイアウト(Locale)',
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
        // TODO pageの登録

        /** @var EntityManager */
        $em = $container->get('doctrine.orm.entity_manager');

        $deviceTypeRepository = $em->getRepository(DeviceType::class);
        $DeviceType = $deviceTypeRepository->find(DeviceType::DEVICE_TYPE_PC);

        foreach ($this->layouts as $name) {
            $Layout = new Layout;
            $Layout->setDeviceType($DeviceType);
            $Layout->setName($name);

            $em->persist($Layout);
            $em->flush();
        }
    }

    private function removeRecord(ContainerInterface $container)
    {
        /** @var EntityManager */
        $em = $container->get('doctrine.orm.entity_manager');

        foreach ($this->layouts as $name) {
            $Layout = $em->getRepository(Layout::class)->findOneBy(['name' => $name]);
            if ($Layout) {
                $em->remove($Layout);
                $em->flush();
            }
        }
    }
}
