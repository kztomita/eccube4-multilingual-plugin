<?php

namespace Plugin\MultiLingual\Controller\Block;

use Eccube\Controller\AbstractController;
use Plugin\MultiLingual\Controller\LocaleTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class SearchProductController extends AbstractController
{
    use LocaleTrait;

    /**
     * @var \Eccube\Controller\Block\SearchProductController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\Block\SearchProductController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * @Route("/{_locale}/block/search_product", name="block_search_product_locale", methods={"GET"})
     * @Route("/{_locale}/block/search_product_sp", name="block_search_product_sp_locale", methods={"GET"})
     * @Template("Block/locale_search_product.twig")
     */
    public function index(Request $request)
    {
        $this->testLocale($request);

        $this->controller->setContainer($this->container);

        return $this->controller->index($request);
    }
}
