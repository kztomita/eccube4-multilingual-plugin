<?php

namespace Plugin\MultiLingual\Form\Extension\Admin;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\ClassName;
use Eccube\Form\Type\Admin\ClassNameType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Admin\ClassNameTypeを拡張する
 */
class ClassNameTypeExtension extends AbstractTypeExtension
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * ClassNameExtension constructor.
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
                ]);
        }

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            // フォームにClassNameモデルがsetData()される際に
            // LocaleClassNameのデータも読み取って初期値として登録

            $form = $event->getForm();

            // 新規作成／編集フォームともAdmin\ClassNameTypeなので、フォーム名で
            // 新規作成／編集の区別をする。
            // 新規作成フォームの名前は'admin_class_name'(ClassNameTypeで定義されている)、
            // 編集フォームの名前は'class_name_<id>'。
            if ($form->getName() == 'admin_class_name') {
                // 新規作成の場合は初期値設定は不要
                return;
            }

            /** @var ClassName $ClassName */
            $ClassName = $form->getData();
            $LocaleClassNames = $ClassName->getLocales();

            foreach ($LocaleClassNames as $LocaleClassName) {
                $locale = $LocaleClassName->getLocale();
                $field = 'name_' . $locale;
                if (!isset($form[$field])) {
                    continue;
                }
                $form[$field]->setData($LocaleClassName->getName());
            }
        });
    }

    public function getExtendedType()
    {
        return ClassNameType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield ClassNameType::class;
    }

}
