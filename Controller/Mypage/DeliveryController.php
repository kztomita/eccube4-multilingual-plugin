<?php

namespace Plugin\MultiLingual\Controller\Mypage;

use Eccube\Controller\AbstractController;
use Eccube\Entity\CustomerAddress;
use Plugin\MultiLingual\Controller\LocaleTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class DeliveryController extends AbstractController
{
    use LocaleTrait;

   /**
     * @var \Eccube\Controller\Mypage\DeliveryController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\Mypage\DeliveryController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * @Route("/{_locale}/mypage/delivery", name="mypage_delivery_locale")
     * @Template("@MultiLingual/default/Mypage/delivery.twig")
     * @Security("is_granted('ROLE_USER')")
     */
    public function index(Request $request)
    {
        $this->testLocale($request);

        return $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );
    }

    /**
     * @Route("/{_locale}/mypage/delivery/new", name="mypage_delivery_new_locale")
     * @Route("/{_locale}/mypage/delivery/{id}/edit", name="mypage_delivery_edit_locale", requirements={"id" = "\d+"})
     * @Template("@MultiLingual/default/Mypage/delivery_edit.twig")
     * @Security("is_granted('ROLE_USER')")
     */
    public function edit(Request $request, $id = null)
    {
        $this->testLocale($request);

        return $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );
    }

    /**
     * @Route("/{_locale}/mypage/delivery/{id}/delete", name="mypage_delivery_delete_locale", methods={"DELETE"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function delete(Request $request, CustomerAddress $CustomerAddress)
    {
        $this->testLocale($request);

        return $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );
    }
}
