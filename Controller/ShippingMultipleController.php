<?php

namespace Plugin\MultiLingual\Controller;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ShippingMultipleController extends AbstractController
{
    use LocaleTrait;

    /**
     * @var \Eccube\Controller\ShippingMultipleController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\ShippingMultipleController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * @Route("/{_locale}/shopping/shipping_multiple", name="shopping_shipping_multiple_default")
     * @Template("@MultiLingual/default/Shopping/shipping_multiple.twig")
     */
    public function index(Request $request)
    {
        $this->testLocale($request);

        return $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );
    }

    /**
     * @Route("/{_locale}/shopping/shipping_multiple_edit", name="shopping_shipping_multiple_edit_locale")
     * @Template("@MultiLingual/default/Shopping/shipping_multiple_edit.twig")
     */
    public function shippingMultipleEdit(Request $request)
    {
        $this->testLocale($request);

        return $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );
    }
}
