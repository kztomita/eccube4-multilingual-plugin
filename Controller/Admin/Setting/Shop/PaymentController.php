<?php

namespace Plugin\MultiLingual\Controller\Admin\Setting\Shop;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Payment;
use Eccube\Util\CacheUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class PaymentController extends AbstractController
{
    /**
     * @var \Eccube\Controller\Admin\Setting\Shop\PaymentController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\Admin\Setting\Shop\PaymentController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * デフォルトのPaymentControllerのテンプレートを差し替える。
     * Route annotationはroute名も含めて、PaymentControllerと全く同じ。
     *
     * @Route("/%eccube_admin_route%/setting/shop/payment/new", name="admin_setting_shop_payment_new")
     * @Route("/%eccube_admin_route%/setting/shop/payment/{id}/edit", requirements={"id" = "\d+"}, name="admin_setting_shop_payment_edit")
     * @Template("@MultiLingual/admin/Setting/Shop/payment_edit.twig")
     */
    public function edit(Request $request, Payment $Payment = null)
    {
        $this->controller->setContainer($this->container);

        return $this->controller->edit($request, $Payment);
    }
}
