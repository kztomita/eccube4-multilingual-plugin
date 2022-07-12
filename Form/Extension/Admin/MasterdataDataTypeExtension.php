<?php

namespace Plugin\MultiLingual\Form\Extension\Admin;

use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Admin\MasterdataDataType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Admin\MasterdataDataTypeを拡張する
 */
class MasterdataDataTypeExtension extends AbstractTypeExtension
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
                    'required' => false,
                    //'mapped' => false,
                ]);
        }
    }

    public function getExtendedType()
    {
        return MasterdataDataType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield MasterdataDataType::class;
    }

}

