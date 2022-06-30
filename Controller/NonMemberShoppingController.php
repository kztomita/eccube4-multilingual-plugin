<?php

namespace Plugin\MultiLingual\Controller;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class NonMemberShoppingController extends AbstractController
{
    use LocaleTrait;

    /**
     * @var \Eccube\Controller\NonMemberShoppingController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\NonMemberShoppingController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * @Route("/{_locale}/shopping/nonmember", name="shopping_nonmember_locale")
     * @Template("@MultiLingual/default/Shopping/nonmember.twig")
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
     * @Route("/{_locale}/shopping/customer", name="shopping_customer_locale")
     */
    public function customer(Request $request)
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
