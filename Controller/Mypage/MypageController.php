<?php

namespace Plugin\MultiLingual\Controller\Mypage;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Product;
use Plugin\MultiLingual\Controller\LocaleTrait;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
     */
    public function login(Request $request, AuthenticationUtils $utils)
    {
        $this->testLocale($request);

        return $this->forwardLocaleRequest(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );
    }

    /**
     * @Route("/{_locale}/mypage/", name="mypage_locale")
     * @Template("MultiLingual/Resource/template/default/Mypage/index.twig")
     * @Security("is_granted('ROLE_USER')")
     */
    public function index(Request $request, Paginator $paginator)
    {
        $this->testLocale($request);

        return $this->forwardLocaleRequest(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );
    }

    /**
     * @Route("/{_locale}/history/{order_no}", name="mypage_history_locale")
     * @Template("MultiLingual/Resource/template/default/Mypage/history.twig")
     * @Security("is_granted('ROLE_USER')")
     */
    public function history(Request $request, $order_no)
    {
        $this->testLocale($request);

        return $this->forwardLocaleRequest(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );
    }

    /**
     * @Route("/{_locale}/mypage/order/{order_no}", name="mypage_order_locale", methods={"PUT"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function order(Request $request, $order_no)
    {
        $this->testLocale($request);

        return $this->forwardLocaleRequest(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );
    }

    /**
     * @Route("/{_locale}/mypage/favorite", name="mypage_favorite_locale")
     * @Template("MultiLingual/Resource/template/default/Mypage/favorite.twig")
     * @Security("is_granted('ROLE_USER')")
     */
    public function favorite(Request $request, Paginator $paginator)
    {
        $this->testLocale($request);

        return $this->forwardLocaleRequest(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );
    }

    /**
     * @Route("/{_locale}/mypage/favorite/{id}/delete", name="mypage_favorite_delete_locale", methods={"DELETE"}, requirements={"id" = "\d+"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function delete(Request $request, Product $Product)
    {
        $this->testLocale($request);

        return $this->forwardLocaleRequest(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );
    }
}
