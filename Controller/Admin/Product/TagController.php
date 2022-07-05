<?php

namespace Plugin\MultiLingual\Controller\Admin\Product;

use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class TagController extends AbstractController
{
    /**
     * @var \Eccube\Controller\Admin\Product\TagController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\Admin\Product\TagController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * デフォルトの規格管理のコントローラのテンプレートを差し替える。
     * Route annotationはroute名も含めて、TagControllerと全く同じ。
     *
     * @Route("/%eccube_admin_route%/product/tag", name="admin_product_tag")
     * @Template("@MultiLingual/admin/Product/tag.twig")
     */
    public function index(Request $request)
    {
        $this->controller->setContainer($this->container);

        return $this->controller->index($request);
    }
}

