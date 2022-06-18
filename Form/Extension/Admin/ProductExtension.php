<?php

namespace Plugin\MultiLingual\Form\Extension\Admin;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Product;
use Eccube\Form\Type\Admin\ProductType;
use Eccube\Form\Validator\TwigLint;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Admin\ProductTypeを拡張する
 */
class ProductExtension extends AbstractTypeExtension
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
                        new Assert\Length(['max' => $this->eccubeConfig['eccube_stext_len']]),
                    ],
                    'mapped' => false,
                ])
                ->add('description_detail_' . $locale, TextareaType::class, [
                    'constraints' => [
                        new Assert\Length(['max' => $this->eccubeConfig['eccube_ltext_len']]),
                    ],
                    'mapped' => false,
                ])
                ->add('description_list_' . $locale, TextareaType::class, [
                    'constraints' => [
                        new Assert\Length(['max' => $this->eccubeConfig['eccube_ltext_len']]),
                    ],
                    'mapped' => false,
                ])
                ->add('free_area_' . $locale, TextareaType::class, [
                    'constraints' => [
                        new TwigLint(),
                    ],
                    'mapped' => false,
                ])
            ;
        }

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            // フォームにProductモデルがsetData()される際に
            // LocaleProductのデータも読み取って初期値として登録

            $form = $event->getForm();

            /** @var Product $Product */
            $Product = $form->getData();
            $LocaleProducts = $Product->getLocales() ?? [];

            foreach ($LocaleProducts as $LocaleProduct) {
                $locale = $LocaleProduct->getLocale();
                $field = 'name_' . $locale;
                if ($form[$field]) {
                    $form[$field]->setData($LocaleProduct->getName());
                }

                $field = 'description_detail_' . $locale;
                if ($form[$field]) {
                    $form[$field]->setData($LocaleProduct->getDescriptionDetail());
                }

                $field = 'description_list_' . $locale;
                if ($form[$field]) {
                    $form[$field]->setData($LocaleProduct->getDescriptionList());
                }

                $field = 'free_area_' . $locale;
                if ($form[$field]) {
                    $form[$field]->setData($LocaleProduct->getFreeArea());
                }
            }
        });
    }

    public function getExtendedType()
    {
        return ProductType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield ProductType::class;
    }

}

