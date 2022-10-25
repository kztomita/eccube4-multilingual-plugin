<?php

namespace Plugin\MultiLingual\Form\Extension\Front;

use Eccube\Entity\Customer;
use Eccube\Form\Type\Front\EntryType;
use Plugin\MultiLingual\Common\LocaleConfigAccessor;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * EntryTypeを拡張する
 */
class EntryTypeExtension extends AbstractTypeExtension
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

                $Customer = $form->getData();
                assert($Customer instanceof Customer);

                // 会員登録時: $Customerは空なので'セイ','メイ'をダミーで登録
                // マイページで情報変更時: $Customerのカナを維持
                $kana01 = $Customer->getKana01() ?? 'セイ';
                $kana02 = $Customer->getKana02() ?? 'メイ';

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
        return EntryType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield EntryType::class;
    }
}
