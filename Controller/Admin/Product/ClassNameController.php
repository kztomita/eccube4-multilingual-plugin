<?php

namespace Plugin\MultiLingual\Controller\Admin\Product;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ClassNameController extends AbstractController
{
    /**
     * @var \Eccube\Controller\Admin\Product\ClassNameController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\Admin\Product\ClassNameController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * デフォルトの規格管理のコントローラのテンプレートを差し替える。
     * Route annotationはroute名も含めて、ClassNameControllerと全く同じ。
     *
     * @Route("/%eccube_admin_route%/product/class_name", name="admin_product_class_name")
     * @Route("/%eccube_admin_route%/product/class_name/{id}/edit", requirements={"id" = "\d+"}, name="admin_product_class_name_edit")
     * @Template("@MultiLingual/admin/Product/class_name.twig")
     */
    public function index(Request $request, $id = null)
    {
        $this->controller->setContainer($this->container);

        return $this->controller->index($request, $id);
    }
}

