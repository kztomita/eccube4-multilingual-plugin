<?php

namespace Plugin\MultiLingual\Form\Extension\Admin;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\ClassCategory;
use Eccube\Form\Type\Admin\ClassCategoryType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Admin\ClassCategoryTypeを拡張する
 */
class ClassCategoryExtension extends AbstractTypeExtension
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * ClassCategoryExtension constructor.
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
            // フォームにClassCategoryモデルがsetData()される際に
            // LocaleClassCategoryのデータも読み取って初期値として登録

            $form = $event->getForm();

            // 新規作成／編集フォームともAdmin\ClassCategoryTypeなので、フォーム名で
            // 新規作成／編集の区別をする。
            // 新規作成フォームの名前は'admin_class_category'(ClassCategoryTypeで定義されている)、
            // 編集フォームの名前は'class_category_<id>'。
            if ($form->getName() == 'admin_class_category') {
                // 新規作成の場合は初期値設定は不要
                return;
            }

            /** @var ClassCategory $ClassCategory */
            $ClassCategory = $form->getData();
            $LocaleClassCategories = $ClassCategory->getLocales();

            foreach ($LocaleClassCategories as $LocaleClassCategory) {
                $locale = $LocaleClassCategory->getLocale();
                $field = 'name_' . $locale;
                if (!isset($form[$field])) {
                    continue;
                }
                $form[$field]->setData($LocaleClassCategory->getName());
            }
        });
    }

    public function getExtendedType()
    {
        return ClassCategoryType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield ClassCategoryType::class;
    }

}
