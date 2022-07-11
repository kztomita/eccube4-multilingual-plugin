<?php

namespace Plugin\MultiLingual\Form\Extension\Admin;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Delivery;
use Eccube\Form\Type\Admin\DeliveryType;
use Plugin\MultiLingual\Entity\LocaleDelivery;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Admin\DeliveryTypeを拡張する
 */
class DeliveryExtension extends AbstractTypeExtension
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
                ->add('name_' . $locale, TextType::class, [
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length([
                            'max' => $this->eccubeConfig['eccube_stext_len'],
                        ]),
                    ],
                    'mapped' => false,
                ])
                ->add('service_name_' . $locale, TextType::class, [
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length([
                            'max' => $this->eccubeConfig['eccube_stext_len'],
                        ]),
                    ],
                    'mapped' => false,
                ])
            ;
        }

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            // フォームにDeliveryモデルがsetData()される際に
            // LocaleDeliveryのデータも読み取って初期値として登録

            $form = $event->getForm();

            /** @var Delivery $Delivery */
            $Delivery = $form->getData();

            if (!$Delivery->getId()) {
                // 新規作成の場合は初期値設定は不要
                return;
            }

            $LocaleDeliveries = $Delivery->getLocales();

            foreach ($LocaleDeliveries as $LocaleDelivery) {
                /** @var LocaleDelivery $LocaleDelivery */
                $locale = $LocaleDelivery->getLocale();
                $field = 'name_' . $locale;
                if (isset($form[$field])) {
                    $form[$field]->setData($LocaleDelivery->getName());
                }
                $field = 'service_name_' . $locale;
                if (isset($form[$field])) {
                    $form[$field]->setData($LocaleDelivery->getServiceName());
                }
            }
        });
    }

    public function getExtendedType()
    {
        return DeliveryType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield DeliveryType::class;
    }

}

