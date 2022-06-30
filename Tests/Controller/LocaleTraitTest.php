<?php

namespace Plugin\MultiLingual\Tests\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Tests\EccubeTestCase;
use Plugin\MultiLingual\Controller\LocaleTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;

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
    private function dumpRoutes(Router $router)
    {
        $collection = $router->getRouteCollection();
        foreach ($collection->all() as $routeId => $route) {
            printf("%s %s\n", $routeId, $route->getPath());
        }
    }

    public function testRedirect()
    {
        #self::bootKernel();
        $container = self::$kernel->getContainer();

        $route = new Route('/{_locale}/shopping');
        $container->get('router')
                  ->getRouteCollection()
                  ->add('shopping_locale', $route);

        /*
        $this->dumpRoutes($container->get('router'));
        $route = $container->get('router')->match('/en/shopping')['_route'];
        print $route;
        */

        $request = new Request;
        $request->setLocale('en');

        $localeController = new LocaleController(new BaseController);
        $localeController->setContainer($container);


        $response = $localeController->redirectToShopping($request);

        $this->assertTrue($response instanceof RedirectResponse);
        $this->assertEquals('/en/shopping', $response->getTargetUrl());
    }
}
