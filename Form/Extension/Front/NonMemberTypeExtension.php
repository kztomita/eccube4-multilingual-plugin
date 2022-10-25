<?php

namespace Plugin\MultiLingual\Form\Extension\Front;

use Eccube\Form\Type\Front\NonMemberType;
use Plugin\MultiLingual\Common\LocaleConfigAccessor;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * NonMemberTypeを拡張する
 */
class NonMemberTypeExtension extends AbstractTypeExtension
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
                $input = $event->getData(); // request data

                $input['kana'] = [
                    'kana01' => 'セイ',
                    'kana02' => 'メイ',
                ];
                $event->setData($input);
            });
        }
    }

    public function getExtendedType()
    {
        return NonMemberType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield NonMemberType::class;
    }
}
