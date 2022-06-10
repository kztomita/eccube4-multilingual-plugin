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
    /**
     * @Route("/{_locale}/products/list", name="product_list_en", methods={"GET"}, requirements={"_locale": "en|cn"})
     * @Template("Product/list.twig")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        return $this->forward('Eccube\Controller\ProductController::index', [
            'request'  => $request,
            'paginator' => $paginator,
        ]);
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
