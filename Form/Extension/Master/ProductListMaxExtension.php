<?php

namespace Plugin\MultiLingual\Form\Extension\Master;

use Eccube\Form\Type\Master\ProductListMaxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Master\ProductListMaxTypeを拡張する
 */
class ProductListMaxExtension extends AbstractMasterTypeExtension
{
    public function getExtendedType()
    {
        return ProductListMaxType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield ProductListMaxType::class;
    }

}

