<?php

namespace Plugin\MultiLingual\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Product;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends AbstractController
{
    use LocaleTrait;

    /**
     * @var \Eccube\Controller\ProductController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\ProductController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * @Route("/{_locale}/products/list", name="product_list_locale", methods={"GET"})
     * @Template("MultiLingual/Resource/template/default/Product/list.twig")
     *
     */
    public function index(Request $request, PaginatorInterface $paginator)
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
     * @Route("/{_locale}/products/detail/{id}", name="product_detail_locale", methods={"GET"}, requirements={"id" = "\d+"})
     * @Template("MultiLingual/Resource/template/default/Product/detail.twig")
     */
    public function detail(Request $request, Product $Product)
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
     * @Route("/{_locale}/products/add_favorite/{id}", name="product_add_favorite_locale", requirements={"id" = "\d+"})
     *
     * テンプレートの存在しないrouteでも、コントローラ内でメッセージが生成される
     * 場合があるので、localeを切り替えるためにrouteを用意する。
     */
    public function addFavorite(Request $request, Product $Product)
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
     * @Route("/{_locale}/products/add_cart/{id}", name="product_add_cart_locale", methods={"POST"}, requirements={"id" = "\d+"})
     */
    public function addCart(Request $request, Product $Product)
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
