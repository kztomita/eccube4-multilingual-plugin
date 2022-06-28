<?php

namespace Plugin\MultiLingual\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Form\Type\Front\EntryType;
use Eccube\Repository\CustomerRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class EntryController extends AbstractController
{
    use LocaleTrait;

    /**
     * @var \Eccube\Controller\EntryController
     */
    private $controller;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    public function __construct(
        \Eccube\Controller\EntryController $c,
        CustomerRepository $customerRepository
    )
    {
        $this->controller = $c;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @Route("/{_locale}/entry", name="entry_locale")
     * @Template("@MultiLingual/default/Entry/index.twig")
     */
    public function index(Request $request)
    {
        $this->testLocale($request);

        /** @var $Customer \Eccube\Entity\Customer */
        $Customer = $this->customerRepository->newCustomer();

        $builder = $this->formFactory->createBuilder(EntryType::class, $Customer);

        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 確認ページは直接Responseを返してくるのでここで横取りして処理する。
            if ($request->get('mode') == 'confirm') {
                return $this->render(
                    '@MultiLingual/default/Entry/confirm.twig',
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
     * @Route("/{_locale}/entry/complete", name="entry_complete_locale")
     * @Template("@MultiLingual/default/Entry/complete.twig")
     */
    public function complete(Request  $request)
    {
        $this->testLocale($request);

        return $this->invokeController(
            $request,
            $this->controller,
            __FUNCTION__,
            []
        );
    }

    /**
     * @Route("/{_locale}/entry/activate/{secret_key}/{qtyInCart}", name="entry_activate_locale")
     * @Template("@MultiLingual/default/Entry/activate.twig")
     */
    public function activate(Request $request, $secret_key, $qtyInCart = null)
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
