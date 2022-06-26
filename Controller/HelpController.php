<?php

namespace Plugin\MultiLingual\Controller;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class HelpController extends AbstractController
{
    use LocaleTrait;

    /**
     * @var \Eccube\Controller\HelpController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\HelpController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * @Route("/{_locale}/help/tradelaw", name="help_tradelaw_locale")
     * @Template("@MultiLingual/default/Help/tradelaw.twig")
     */
    public function tradelaw(Request $request)
    {
        $this->testLocale($request);

        return $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            []
        );
    }

    /**
     * @Route("/{_locale}/guide", name="help_guide_locale")
     * @Template("@MultiLingual/default/Help/guide.twig")
     */
    public function guide(Request $request)
    {
        $this->testLocale($request);

        return $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            []
        );
    }

    /**
     * @Route("/{_locale}/help/about", name="help_about_locale")
     * @Template("@MultiLingual/default/Help/about.twig")
     */
    public function about(Request $request)
    {
        $this->testLocale($request);

        return $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            []
        );
    }

    /**
     * @Route("/{_locale}/help/privacy", name="help_privacy_locale")
     * @Template("@MultiLingual/default/Help/privacy.twig")
     */
    public function privacy(Request $request)
    {
        $this->testLocale($request);

        return $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            []
        );
    }

    /**
     * @Route("/{_locale}/help/agreement", name="help_agreement_locale")
     * @Template("@MultiLingual/default/Help/agreement.twig")
     */
    public function agreement(Request $request)
    {
        $this->testLocale($request);

        return $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            []
        );
    }
}

