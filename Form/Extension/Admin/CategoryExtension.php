<?php

namespace Plugin\MultiLingual\Form\Extension\Admin;

use Doctrine\Common\Collections\Collection;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Category;
use Eccube\Form\Type\Admin\CategoryType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Admin\CategoryTypeを拡張する
 * Ref to:
 * https://doc4.ec-cube.net/customize_formtype
 * https://symfony.com/doc/current/form/create_form_type_extension.html
 */
class CategoryExtension extends AbstractTypeExtension
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * CategoryType constructor.
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
            // フォームにCategoryモデルがsetData()される際に
            // LocaleCategoryのデータも読み取って初期値として登録

            $form = $event->getForm();

            // 新規作成／編集フォームともAdmin\CategoryTypeなので、フォーム名で
            // 新規作成／編集の区別をする。
            // 新規作成フォームの名前は'admin_category'(CategoryTypeで定義されている)、
            // 編集フォームの名前は'category_<cat id>'。
            if ($form->getName() == 'admin_category') {
                // 新規作成の場合は初期値設定は不要
                return;
            }

            /** @var Category $Category */
            $Category = $form->getData();
            $LocaleCategoeis = $Category->getLocales();

            foreach ($LocaleCategoeis as $LocaleCategory) {
                $locale = $LocaleCategory->getLocale();
                $field = 'name_' . $locale;
                if (!isset($form[$field])) {
                    continue;
                }
                $form[$field]->setData($LocaleCategory->getName());
            }
        });
    }

    public function getExtendedType()
    {
        return CategoryType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield CategoryType::class;
    }

}
