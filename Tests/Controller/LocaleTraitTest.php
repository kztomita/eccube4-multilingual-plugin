<?php

namespace Plugin\MultiLingual\Tests\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Tests\EccubeTestCase;
use Plugin\MultiLingual\Controller\LocaleTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends AbstractController
{
    public function redirectToShopping()
    {
        return $this->redirectToRoute('shopping');
    }
}

class LocaleController extends AbstractController
{
    use LocaleTrait;

    /**
     * @var BaseController
     */
    private $controller;

    public function __construct(
        BaseController $c
    )
    {
        $this->controller = $c;
    }

    public function redirectToShopping(Request $request)
    {
        return $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            []
        );
    }
}

class LocaleTraitTest extends EccubeTestCase
{
    public function testRedirect()
    {
        #self::bootKernel();
        $container = self::$kernel->getContainer();

        /// pluginを有効にしておかないといけない？
        $router = $container->get('router');
        $route = $router->match('/shopping')['_route'];
        //$route = $router->match('/en/shopping')['_route'];
        print $route;
        ///

        $request = new Request;
        $request->setLocale('en');

        $localeController = new LocaleController(new BaseController);
        $localeController->setContainer($container);

        $response = $localeController->redirectToShopping($request);

        $this->assertTrue($response instanceof RedirectResponse);

        $this->assertEquals('/en/shopping', $response->getTargetUrl());
    }
}
