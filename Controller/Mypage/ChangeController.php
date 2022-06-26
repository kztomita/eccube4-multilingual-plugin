<?php

namespace Plugin\MultiLingual\Controller\Mypage;

use Eccube\Controller\AbstractController;
use Plugin\MultiLingual\Controller\LocaleTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
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
     * @Route("/{_locale}/mypage/change_complete", name="mypage_change_complete_locale")
     * @Template("MultiLingual/Resource/template/default/Mypage/change_complete.twig")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function complete(Request $request)
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

