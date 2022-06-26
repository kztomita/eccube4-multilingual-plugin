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

        $this->controller->setContainer($this->container);

        return $this->controller->index($request);
    }
}