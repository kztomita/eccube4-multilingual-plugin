<?php

namespace Plugin\MultiLingual\Form\Extension;

use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Admin\CategoryType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
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
            $builder->add('name_' . $locale, TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    ]),
                ],
                'mapped' => false,
            ]);
        }
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
