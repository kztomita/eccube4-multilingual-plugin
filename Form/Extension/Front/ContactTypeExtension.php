<?php

namespace Plugin\MultiLingual\Form\Extension\Front;

use Eccube\Form\Type\Front\ContactType;
use Plugin\MultiLingual\Common\LocaleConfigAccessor;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * ContactTypeを拡張する
 */
class ContactTypeExtension extends AbstractTypeExtension
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

                // 問い合わせフォームではカナは省略可能なので空欄にしておく
                $input['kana'] = [
                    'kana01' => '',
                    'kana02' => '',
                ];
                $event->setData($input);
            });
        }
    }

    public function getExtendedType()
    {
        return ContactType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield ContactType::class;
    }
}
