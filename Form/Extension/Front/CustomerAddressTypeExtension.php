<?php

namespace Plugin\MultiLingual\Form\Extension\Front;
use Eccube\Entity\Customer;
use Eccube\Entity\CustomerAddress;
use Eccube\Form\Type\Front\CustomerAddressType;
use Plugin\MultiLingual\Common\LocaleConfigAccessor;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * CustomerAddressTypeを拡張する
 */
class CustomerAddressTypeExtension extends AbstractTypeExtension
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

                $input['kana'] = [
                    'kana01' => $CustomerAddress->getKana01(),
                    'kana02' => $CustomerAddress->getKana02(),
                ];
                $event->setData($input);
            });
        }
    }

    public function getExtendedType()
    {
        return CustomerAddressType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield CustomerAddressType::class;
    }
}
