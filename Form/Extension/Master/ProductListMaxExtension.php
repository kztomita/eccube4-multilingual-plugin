<?php

namespace Plugin\MultiLingual\Form\Extension\Master;

use Eccube\Form\Type\Master\ProductListMaxType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Master\ProductListMaxTypeを拡張する
 */
class ProductListMaxExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_label' => 'locale_name',
        ]);
    }

    public function getExtendedType()
    {
        return ProductListMaxType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield ProductListMaxType::class;
    }

}

