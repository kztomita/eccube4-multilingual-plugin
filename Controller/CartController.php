<?php

namespace Plugin\MultiLingual\Controller;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class CartController extends AbstractController
{
    use LocaleTrait;

    /**
     * @var \Eccube\Controller\CartController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\CartController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * @Route("/{_locale}/cart", name="cart_locale")
     * @Template("@MultiLingual/default/Cart/index.twig")

     */
    public function login(Request $request)
    {
        $this->testLocale($request);

        return $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );
    }
    // TODO その他のメソッド
}
