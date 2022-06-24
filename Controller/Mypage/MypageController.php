<?php

namespace Plugin\MultiLingual\Controller\Mypage;

use Eccube\Controller\AbstractController;
use Knp\Component\Pager\PaginatorInterface;
use Plugin\MultiLingual\Controller\LocaleTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class MypageController extends AbstractController
{
    use LocaleTrait;

    /**
     * @var \Eccube\Controller\Mypage\MypageController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\Mypage\MypageController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * @Route("/{_locale}/mypage/login", name="mypage_login_locale")
     * @Template("MultiLingual/Resource/template/default/Mypage/login.twig")
     */
    public function login(Request $request, AuthenticationUtils $utils)
    {
        $this->testLocale($request);

        $this->controller->setContainer($this->container);

        return $this->controller->login($request, $utils);
    }
}
