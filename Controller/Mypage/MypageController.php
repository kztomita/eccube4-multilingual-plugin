<?php

namespace Plugin\MultiLingual\Controller\Mypage;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Product;
use Plugin\MultiLingual\Controller\LocaleTrait;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
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

    /**
     * @Route("/{_locale}/mypage/", name="mypage_locale")
     * @Template("MultiLingual/Resource/template/default/Mypage/index.twig")
     */
    public function index(Request $request, Paginator $paginator)
    {
        $this->testLocale($request);

        $this->controller->setContainer($this->container);

        return $this->controller->index($request, $paginator);
    }

    /**
     * @Route("/{_locale}/history/{order_no}", name="mypage_history_locale")
     * @Template("MultiLingual/Resource/template/default/Mypage/history.twig")
     */
    public function history(Request $request, $order_no)
    {
        $this->testLocale($request);

        $this->controller->setContainer($this->container);

        return $this->controller->history($request, $order_no);
    }

    /**
     * @Route("/{_locale}/mypage/order/{order_no}", name="mypage_order_locale", methods={"PUT"})
     */
    public function order(Request $request, $order_no)
    {
        $this->testLocale($request);

        $this->controller->setContainer($this->container);

        // TODO Localeページへのリダイレクト処理が必要？
        return $this->controller->order($request, $order_no);
    }

    /**
     * @Route("/{_locale}/mypage/favorite", name="mypage_favorite_locale")
     * @Template("MultiLingual/Resource/template/default/Mypage/favorite.twig")
     */
    public function favorite(Request $request, Paginator $paginator)
    {
        $this->testLocale($request);

        $this->controller->setContainer($this->container);

        return $this->controller->favorite($request, $paginator);
    }

    /**
     * @Route("/{_locale}/mypage/favorite/{id}/delete", name="mypage_favorite_delete_locale", methods={"DELETE"}, requirements={"id" = "\d+"})
     */
    public function delete(Request $request, Product $Product)
    {
        $this->testLocale($request);

        $this->controller->setContainer($this->container);

        // TODO Localeページへのリダイレクト処理が必要？
        return $this->controller->delete($request, $Product);
    }
}
