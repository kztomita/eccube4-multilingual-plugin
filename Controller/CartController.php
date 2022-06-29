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
     * @Route(
     *     path="/{_locale}/cart/{operation}/{productClassId}",
     *     name="cart_handle_item_locale",
     *     methods={"PUT"},
     *     requirements={
     *          "operation": "up|down|remove",
     *          "productClassId": "\d+"
     *     }
     * )
     */
    public function handleCartItem(Request $request, $operation, $productClassId)
    {
        $this->testLocale($request);

        return $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            [$operation, $productClassId]
        );
    }

    /**
     * @Route("/{_locale}/cart/buystep/{cart_key}", name="cart_buystep_locale", requirements={"cart_key" = "[a-zA-Z0-9]+[_][\x20-\x7E]+"})
     */
    public function buystep(Request $request, $cart_key)
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
