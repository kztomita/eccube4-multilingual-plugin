<?php

namespace Plugin\MultiLingual\Controller\Mypage;

use Eccube\Controller\AbstractController;
use Plugin\MultiLingual\Controller\LocaleTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ChangeController extends AbstractController
{
    use LocaleTrait;

   /**
     * @var \Eccube\Controller\Mypage\ChangeController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\Mypage\ChangeController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * @Route("/{_locale}/mypage/change", name="mypage_change_locale")
     * @Template("MultiLingual/Resource/template/default/Mypage/change.twig")
     */
    public function index(Request $request)
    {
        $this->testLocale($request);

        $this->controller->setContainer($this->container);

        $result = $this->controller->index($request);
        if ($result instanceof RedirectResponse) {
            $result->setTargetUrl('/' . $request->getLocale() . $result->getTargetUrl());
        }
        return $result;
    }

    /**
     * @Route("/{_locale}/mypage/change_complete", name="mypage_change_complete_locale")
     * @Template("MultiLingual/Resource/template/default/Mypage/change_complete.twig")
     */
    public function complete(Request $request)
    {
        $this->testLocale($request);

        $this->controller->setContainer($this->container);

        return $this->controller->complete($request);
    }
}

