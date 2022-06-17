<?php

namespace Plugin\MultiLingual\Controller\Admin\Product;

use Eccube\Controller\AbstractController;
use Eccube\Util\CacheUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class ProductController extends AbstractController
{
    /**
     * @var \Eccube\Controller\Admin\Product\ProductController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\Admin\Product\ProductController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * デフォルトの商品登録のコントローラのテンプレートを差し替える。
     * Route annotationはroute名も含めて、ProductControllerと全く同じ。
     *
     * @Route("/%eccube_admin_route%/product/product/new", name="admin_product_product_new", methods={"GET", "POST"})
     * @Route("/%eccube_admin_route%/product/product/{id}/edit", requirements={"id" = "\d+"}, name="admin_product_product_edit", methods={"GET", "POST"})
     * @Template("MultiLingual/Resource/template/admin/Product/product.twig")
     */
    public function edit(Request $request, $id = null, RouterInterface $router, CacheUtil $cacheUtil)
    {
        $this->controller->setContainer($this->container);

        return $this->controller->edit($request, $id, $router, $cacheUtil);
    }
}

