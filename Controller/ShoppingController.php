<?php

namespace Plugin\MultiLingual\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Order;
use Eccube\Entity\Shipping;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Plugin\MultiLingual\Service\OrderHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class ShoppingController extends AbstractController
{
    use LocaleTrait;

    /**
     * @var \Eccube\Controller\ShoppingController
     */
    private $controller;

    /**
     * @var OrderHelper
     */
    private $orderHelper;

    public function __construct(
        \Eccube\Controller\ShoppingController $c,
        OrderHelper $orderHelper
    )
    {
        $this->controller = $c;
        $this->orderHelper = $orderHelper;
    }

    /**
     * @Route("/{_locale}/shopping", name="shopping_locale")
     * @Template("@MultiLingual/default/Shopping/index.twig")
     */
    public function index(Request $request, PurchaseFlow $cartPurchaseFlow)
    {
        $this->testLocale($request);

        $result = $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            [$cartPurchaseFlow]
        );
        if ($result instanceof RedirectResponse) {
            return $result;
        }

        /** @var Order $Order */
        $Order = $result['Order'];
        $this->orderHelper->setLocaleNameAsOrder($Order);

        return $result;
    }

    /**
     * @Route("/{_locale}/shopping/redirect_to", name="shopping_redirect_to_locale", methods={"POST"})
     * @Template("@MultiLingual/default/Shopping/index.twig")
     */
    public function redirectTo(Request $request, RouterInterface $router)
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
     * @Route("/{_locale}/shopping/confirm", name="shopping_confirm_locale", methods={"POST"})
     * @Template("@MultiLingual/default/Shopping/confirm.twig")
     */
    public function confirm(Request $request)
    {
        $this->testLocale($request);

        $result = $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );

        /** @var Order $Order */
        $Order = $result['Order'];
        $this->orderHelper->setLocaleNameAsOrder($Order);

        return $result;
    }

    /**
     * @Route("/{_locale}/shopping/checkout", name="shopping_checkout_locale", methods={"POST"})
     * @Template("@MultiLingual/default/Shopping/confirm.twig")
     */
    public function checkout(Request $request)
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
     * @Route("/{_locale}/shopping/complete", name="shopping_complete_locale")
     * @Template("@MultiLingual/default/Shopping/complete.twig")
     */
    public function complete(Request $request)
    {
        $this->testLocale($request);

        $result = $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );

        /** @var Order $Order */
        $Order = $result['Order'];
        $this->orderHelper->setLocaleNameAsOrder($Order);

        return $result;
    }

    /**
     * @Route("/{_locale}/shopping/shipping/{id}", name="shopping_shipping_locale", requirements={"id" = "\d+"})
     * @Template("@MultiLingual/default/Shopping/shipping.twig")
     */
    public function shipping(Request $request, Shipping $Shipping)
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
     * @Route("/{_locale}/shopping/shipping_edit/{id}", name="shopping_shipping_edit_locale", requirements={"id" = "\d+"})
     * @Template("@MultiLingual/default/Shopping/shipping_edit.twig")
     */
    public function shippingEdit(Request $request, Shipping $Shipping)
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
     * @Route("/{_locale}/shopping/login", name="shopping_login_locale")
     * @Template("@MultiLingual/default/Shopping/login.twig")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
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
     * @Route("/{_locale}/shopping/error", name="shopping_error_locale")
     * @Template("@MultiLingual/default/Shopping/shopping_error.twig")
     */
    public function error(Request $request, PurchaseFlow $cartPurchaseFlow)
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
