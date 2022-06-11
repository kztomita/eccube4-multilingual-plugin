<?php

namespace Plugin\MultiLingual;

use Eccube\Entity\Layout;
use Eccube\Entity\Master\DeviceType;
use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PluginManager extends AbstractPluginManager
{
    const LAYOUT_NAME = 'トップページ用レイアウト(Locale)';

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

        $Layout = new Layout;

        $DeviceType = $deviceTypeRepository->find(DeviceType::DEVICE_TYPE_PC);
        $Layout->setDeviceType($DeviceType);
        $Layout->setName(self::LAYOUT_NAME);

        $em->persist($Layout);
        $em->flush();
    }

    private function removeRecord(ContainerInterface $container)
    {
        /** @var EntityManager */
        $em = $container->get('doctrine.orm.entity_manager');

        $Layout = $em->getRepository(Layout::class)->findOneBy(['name' => self::LAYOUT_NAME]);
        if ($Layout) {
            $em->remove($Layout);
            $em->flush();
        }
    }
}
