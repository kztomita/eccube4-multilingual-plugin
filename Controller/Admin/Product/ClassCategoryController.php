<?php

namespace Plugin\MultiLingual\Controller\Admin\Product;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ClassCategoryController extends AbstractController
{
    /**
     * @var \Eccube\Controller\Admin\Product\ClassCategoryController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\Admin\Product\ClassCategoryController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * デフォルトの規格管理のコントローラのテンプレートを差し替える。
     * Route annotationはroute名も含めて、ClassCategoryControllerと全く同じ。
     *
     * @Route("/%eccube_admin_route%/product/class_category/{class_name_id}", requirements={"class_name_id" = "\d+"}, name="admin_product_class_category")
     * @Route("/%eccube_admin_route%/product/class_category/{class_name_id}/{id}/edit", requirements={"class_name_id" = "\d+", "id" = "\d+"}, name="admin_product_class_category_edit")
     * @Template("@MultiLingual/admin/Product/class_category.twig")
     */
    public function index(Request $request, $class_name_id, $id = null)
    {
        $this->controller->setContainer($this->container);

        return $this->controller->index($request, $class_name_id, $id);
    }
}

