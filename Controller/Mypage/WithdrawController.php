<?php

namespace Plugin\MultiLingual\Controller\Mypage;

use Eccube\Controller\AbstractController;
use Plugin\MultiLingual\Controller\LocaleTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class WithdrawController extends AbstractController
{
    use LocaleTrait;

    /**
     * @var \Eccube\Controller\Mypage\WithdrawController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\Mypage\WithdrawController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * @Route("/{_locale}/mypage/withdraw", name="mypage_withdraw_locale")
     * @Template("@MultiLingual/default/Mypage/withdraw.twig")
     * @Security("is_granted('ROLE_USER')")
     */
    public function index(Request $request)
    {
        $this->testLocale($request);

        $builder = $this->formFactory->createBuilder();

        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 確認ページは直接Responseを返してくるのでここで横取りして処理する。
            if ($request->get('mode') == 'confirm') {
                return $this->render(
                    '@MultiLingual/default/Mypage/withdraw_confirm.twig',
                    [
                        'form' => $form->createView(),
                    ]
                );
            }
        }

        return $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            func_get_args()
        );
    }

    /**
     * @Route("/{_locale}/mypage/withdraw_complete", name="mypage_withdraw_complete_locale")
     * @Template("@MultiLingual/default/Mypage/withdraw_complete.twig")
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
     */
    public function complete(Request $request)
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
