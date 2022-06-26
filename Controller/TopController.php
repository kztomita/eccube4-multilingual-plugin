<?php

namespace Plugin\MultiLingual\Controller;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class TopController extends AbstractController
{
    use LocaleTrait;

    /**
     * @var \Eccube\Controller\TopController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\TopController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * @Route("/{_locale}/home", name="homepage_locale", methods={"GET"})
     * @Template("MultiLingual/Resource/template/default/index.twig")
     */
    public function index(Request $request)
    {
        $this->testLocale($request);

        return $this->forwardLocaleRequest(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );
    }
}
