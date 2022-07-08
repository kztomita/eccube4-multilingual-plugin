<?php

namespace Plugin\MultiLingual\Controller\Admin\Setting\Shop;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Payment;
use Eccube\Util\CacheUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class DeliveryController extends AbstractController
{
    /**
     * @var \Eccube\Controller\Admin\Setting\Shop\DeliveryController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\Admin\Setting\Shop\DeliveryController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * デフォルトのDeliveryControllerのテンプレートを差し替える。
     * Route annotationはroute名も含めて、DeliveryControllerと全く同じ。
     *
     * @Route("/%eccube_admin_route%/setting/shop/delivery/new", name="admin_setting_shop_delivery_new")
     * @Route("/%eccube_admin_route%/setting/shop/delivery/{id}/edit", requirements={"id" = "\d+"}, name="admin_setting_shop_delivery_edit")
     * @Template("@MultiLingual/admin/Setting/Shop/delivery_edit.twig")
     */
    public function edit(Request $request, $id = null)
    {
        $this->controller->setContainer($this->container);

        return $this->controller->edit($request, $id);
    }
}
