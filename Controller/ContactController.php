<?php

namespace Plugin\MultiLingual\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Form\Type\Front\ContactType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends AbstractController
{
    use LocaleTrait;

    /**
     * @var \Eccube\Controller\ContactController
     */
    private $controller;

    public function __construct(
        \Eccube\Controller\ContactController $c
    )
    {
        $this->controller = $c;
    }

    /**
     * TODO メールの文面を切り替えたいので転送せずここで処理する？
     *
     * @Route("/{_locale}/contact", name="contact_locale")
     * @Template("@MultiLingual/default/Contact/index.twig")
     */
    public function index(Request $request)
    {
        $this->testLocale($request);

        $builder = $this->formFactory->createBuilder(ContactType::class);

        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            error_log('submitted');
            // 確認ページは直接Responseを返してくるのでここで横取りして処理する。
            if ($request->get('mode') == 'confirm') {
                error_log('confirm');
                return $this->render(
                    '@MultiLingual/default/Contact/confirm.twig',
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
     * @Route("/{_locale}/contact/complete", name="contact_complete_locale")
     * @Template("@MultiLingual/default/Contact/complete.twig")
     */
    public function complete(Request $request)
    {
        $this->testLocale($request);

        return $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            []
        );
    }
}
