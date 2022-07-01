<?php

namespace Plugin\MultiLingual\Controller;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ForgotController extends AbstractController
{
    use LocaleTrait;

    /**
     * @var \Eccube\Controller\ForgotController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\ForgotController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * @Route("/{_locale}/forgot", name="forgot_locale")
     * @Template("@MultiLingual/default/Forgot/index.twig")
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
     * @Route("/{_locale}/forgot/complete", name="forgot_complete_locale")
     * @Template("@MultiLingual/default/Forgot/complete.twig")
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

    /**
     * @Route("/{_locale}/forgot/reset/{reset_key}", name="forgot_reset_locale")
     * @Template("@MultiLingual/default/Forgot/reset.twig")
     */
    public function reset(Request $request, $reset_key)
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

