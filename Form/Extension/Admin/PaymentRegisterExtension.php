<?php

namespace Plugin\MultiLingual\Form\Extension\Admin;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Payment;
use Eccube\Form\Type\Admin\PaymentRegisterType;
use Plugin\MultiLingual\Entity\LocalePayment;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Admin\PaymentRegisterTypeを拡張する
 */
class PaymentRegisterExtension extends AbstractTypeExtension
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * PaymentRegisterExtension constructor.
     *
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->eccubeConfig = $eccubeConfig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            $builder
                ->add('method_' . $locale, TextType::class, [
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length([
                            'max' => $this->eccubeConfig['eccube_stext_len'],
                        ]),
                    ],
                    'mapped' => false,
                ]);
        }

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            // フォームにPaymentモデルがsetData()される際に
            // LocalePaymentのデータも読み取って初期値として登録

            $form = $event->getForm();

            /** @var Payment $Payment */
            $Payment = $form->getData();

            if (!$Payment->getId()) {
                // 新規作成の場合は初期値設定は不要
                return;
            }

            $LocalePayments = $Payment->getLocales();

            foreach ($LocalePayments as $LocalePayment) {
                /** @var LocalePayment $LocalePayment */
                $locale = $LocalePayment->getLocale();
                $field = 'method_' . $locale;
                if (!isset($form[$field])) {
                    continue;
                }
                $form[$field]->setData($LocalePayment->getMethod());
            }
        });
    }

    public function getExtendedType()
    {
        return PaymentRegisterType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield PaymentRegisterType::class;
    }

}

