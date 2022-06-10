<?php

namespace Plugin\MultiLingual\Controller;

use Eccube\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

// TODO compositionにできないか
class ProductController extends \Eccube\Controller\ProductController
{
    /**
     *
     * @Route("/{_locale}/products/detail/{id}", name="product_detail_locale", methods={"GET"}, requirements={"id" = "\d+", "_locale": "en|cn",})
     * @Template("Product/detail.twig")
     */
    public function detail(Request $request, Product $product)
    {
        return parent::detail($request, $product);
    }
}
