<?php

namespace Plugin\MultiLingual\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends AbstractController
{
    /**
     *
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
