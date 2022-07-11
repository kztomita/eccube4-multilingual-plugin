<?php


namespace Plugin\MultiLingual\Form\Extension\Admin;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Delivery;
use Eccube\Entity\DeliveryTime;
use Eccube\Form\Type\Admin\DeliveryTimeType;
use Eccube\Form\Type\Admin\DeliveryType;
use Plugin\MultiLingual\Entity\LocaleDelivery;
use Plugin\MultiLingual\Entity\LocaleDeliveryTime;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Admin\DeliveryTimeTypeを拡張する
 */
class DeliveryTimeExtension extends AbstractTypeExtension
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
                ->add('delivery_time_' . $locale, TextType::class, [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'common.select',
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'mapped' => false,
                ]);
        }

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            // フォームにDeliveryTimeモデルがsetData()される際に
            // LocaleDeliveryTimeのデータも読み取って初期値として登録

            $form = $event->getForm();

            /** @var DeliveryTime $DeliveryTime */
            $DeliveryTime = $form->getData();
            if (!$DeliveryTime) {
                // $form->getName() == '__name__'
                // CollectionTypeのprototypeについても呼ばれる。
                return;
            }

            $LocaleDeliveryTimes = $DeliveryTime->getLocales();
            foreach ($LocaleDeliveryTimes as $LocaleDeliveryTime) {
                /** @var LocaleDeliveryTime $LocaleDeliveryTime */
                $locale = $LocaleDeliveryTime->getLocale();
                $field = 'delivery_time_' . $locale;
                if (!isset($form[$field])) {
                    continue;
                }
                $form[$field]->setData($LocaleDeliveryTime->getDeliveryTime());
            }
        });
    }

    public function getExtendedType()
    {
        return DeliveryTimeType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield DeliveryTimeType::class;
    }

}
