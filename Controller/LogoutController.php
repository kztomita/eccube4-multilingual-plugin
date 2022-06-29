<?php

namespace Plugin\MultiLingual\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class LogoutController extends AbstractController
{
    use LocaleTrait;

    /**
     * Logout用コントローラ。
     * 元々のログアウト処理は
     * app/config/eccube/packages/security.yaml
     * でURLを指定しているだけでコントローラはない。
     * routeについてもapp/config/eccube/routes.yamlで定義されているだけ。
     *
     * Locale対応のログアウトについてもsecurity.yamlでURLを指定すればコントローラは
     * 必要ないが、route名が欲しいためここでコントローラを定義する(routes.yamlの
     * 変更はしたくない)。
     *
     * @Route("/{_locale}/logout", name="logout_locale")
     */
    public function index()
    {
        return [];
    }
}

