<?php

namespace Plugin\MultiLingual\Controller;

use Eccube\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

trait LocaleTrait
{
    /**
     * Annotationでrequirements={"_locale": "en|cn"}のように静的に
     * localeを制限するのではなく、parameterで指定できるようにする。
     *
     * @param Request $request
     */
    public function testLocale(Request $request)
    {
        $locales = $this->eccubeConfig['multi_lingual_locales'];

        if (!in_array($request->getLocale(), $locales)) {
            throw $this->createNotFoundException('Unsupported locale.');
        }
   }

    /**
     * 別のコントローラーへのリクエストを転送する。
     * 転送先のコントローラからRedirectResponseが返された場合は、localeページのURLに書き換える。
     *
     * forward()だと転送先のactionでannotationしたテンプレートが使われる。
     * テンプレートを分けたい場合は本メソッドを使う。
     *
     * https://stackoverflow.com/questions/17611447/forward-with-another-template
     *
     * @param Request $request
     * @param AbstractController $controller
     * @param string $method
     * @param array $args
     * @return mixed|RedirectResponse
     */
   public function forwardLocaleRequest(Request $request, AbstractController $controller, string $method, array $args)
   {
       $controller->setContainer($this->container);

       $result = call_user_func_array([$controller, $method], $args);
       if ($result instanceof RedirectResponse) {
           $localeUrl = '/' . $request->getLocale() . $result->getTargetUrl();

           /** @var Router $router */
           $router = $this->get('router');
           try {
               $route = $router->match($localeUrl)['_route'];
               //error_log($route);
           } catch (\Exception $e) {
               // マッチするrouteがなければそのまま返す
               return $result;
           }
           $result->setTargetUrl($localeUrl);
       }
       return $result;
   }
}
