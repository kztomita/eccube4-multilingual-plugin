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

        return $this->forwardLocaleRequest(
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

        return $this->forwardLocaleRequest(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );
    }
}
