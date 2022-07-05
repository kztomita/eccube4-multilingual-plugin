<?php

namespace Plugin\MultiLingual\Form\Extension\Admin;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Tag;
use Eccube\Form\Type\Admin\ProductTag;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Admin\ProductTagを拡張する <-- Admin\TagTypeではないので注意。
 */
class ProductTagExtension extends AbstractTypeExtension
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * ProductTagExtension constructor.
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
            // フォームにTagモデルがsetData()される際に
            // LocaleTagのデータも読み取って初期値として登録

            $form = $event->getForm();

            // 新規作成／編集フォームともAdmin\TagTypeなので、フォーム名で
            // 新規作成／編集の区別をする。
            // 新規作成フォームの名前は'admin_tag'(TagTypeで定義されている)、
            // 編集フォームの名前は'tag_<id>'。
            if ($form->getName() == 'admin_tag') {
                // 新規作成の場合は初期値設定は不要
                return;
            }

            /** @var Tag $Tag */
            $Tag = $form->getData();
            $LocaleTags = $Tag->getLocales();

            foreach ($LocaleTags as $LocaleTag) {
                $locale = $LocaleTag->getLocale();
                $field = 'name_' . $locale;
                if (!$form[$field]) {
                    continue;
                }
                $form[$field]->setData($LocaleTag->getName());
            }
        });
    }

    public function getExtendedType()
    {
        return ProductTag::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield ProductTag::class;
    }

}
