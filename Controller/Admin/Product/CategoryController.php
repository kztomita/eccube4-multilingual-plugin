<?php

namespace Plugin\MultiLingual\Controller\Admin\Product;

use Eccube\Controller\AbstractController;
use Eccube\Util\CacheUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends AbstractController
{
    /**
     * @var \Eccube\Controller\Admin\Product\CategoryController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\Admin\Product\CategoryController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * デフォルトのカテゴリ管理のコントローラのテンプレートを差し替える。
     * Route annotationはroute名も含めて、CategoryControllerと全く同じ。
     *
     * @Route("/%eccube_admin_route%/product/category", name="admin_product_category", methods={"GET", "POST"})
     * @Route("/%eccube_admin_route%/product/category/{parent_id}", requirements={"parent_id" = "\d+"}, name="admin_product_category_show", methods={"GET", "POST"})
     * @Route("/%eccube_admin_route%/product/category/{id}/edit", requirements={"id" = "\d+"}, name="admin_product_category_edit", methods={"GET", "POST"})
     * @Template("MultiLingual/Resource/template/admin/Product/category.twig")
     */
    public function index(Request $request, $parent_id = null, $id = null, CacheUtil $cacheUtil)
    {
        $this->controller->setContainer($this->container);

        return $this->controller->index($request, $parent_id, $id, $cacheUtil);
    }
}

