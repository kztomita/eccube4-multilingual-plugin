<?php

namespace Plugin\MultiLingual\Form\Extension\Front;

use Eccube\Entity\CustomerAddress;
use Eccube\Form\Type\Front\ShoppingShippingType;
use Plugin\MultiLingual\Common\LocaleConfigAccessor;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * ShoppingShippingTypeTypeを拡張する
 */
class ShoppingShippingTypeExtension extends AbstractTypeExtension
{
    /**
     * @var LocaleConfigAccessor
     */
    protected $localeConfigAccessor;

    public function __construct(LocaleConfigAccessor $localeConfigAccessor)
    {
        $this->localeConfigAccessor = $localeConfigAccessor;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $localeConfig = $this->localeConfigAccessor->get();
        if (!$localeConfig) {
            return null;
        }

        if (!$localeConfig['input_kana']) {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $input = $event->getData(); // request data

                $CustomerAddress = $form->getData();
                assert($CustomerAddress instanceof CustomerAddress);

                $kana01 = $CustomerAddress->getKana01() ?? 'セイ';
                $kana02 = $CustomerAddress->getKana02() ?? 'メイ';

                $input['kana'] = [
                    'kana01' => $kana01,
                    'kana02' => $kana02,
                ];
                $event->setData($input);
            });
        }
    }

    public function getExtendedType()
    {
        return ShoppingShippingType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield ShoppingShippingType::class;
    }
}
