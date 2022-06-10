<?php

namespace Plugin\MultiLingual\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Product;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/*
 * コントローラはオリジナルのものを利用する。
 * forward()すると転送先のactionでannotationしたテンプレートが使われる。
 * テンプレートを分けたい場合は、コントローラを生成して手動で呼び出す。
 *
 * https://stackoverflow.com/questions/17611447/forward-with-another-template
 */

class ProductController extends AbstractController
{
    /**
     * @var \Eccube\Controller\ProductController
     */
    private $controller;

    public function __construct(\Eccube\Controller\ProductController $c)
    {
        $this->controller = $c;
    }

    /**
     * @Route("/{_locale}/products/list", name="product_list_locale", methods={"GET"}, requirements={"_locale": "en|cn"})
     * @Template("@MultiLingual/Product/list.twig")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        return $this->controller->index($request, $paginator);
    }

    /**
     * @Route("/{_locale}/products/detail/{id}", name="product_detail_locale", methods={"GET"}, requirements={"id" = "\d+", "_locale": "en|cn",})
     * @Template("Product/detail.twig")
     */
    public function detail(Request $request, Product $Product)
    {
        // ref. to https://symfony.com/doc/current/controller/forwarding.html
        return $this->forward('Eccube\Controller\ProductController::detail', [
            'request'  => $request,
            'Product' => $Product,
        ]);
    }
}
