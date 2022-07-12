<?php

namespace Plugin\MultiLingual\Form\Extension\Master;

use Eccube\Form\Type\Master\ProductListOrderByType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Master\ProductListOrderByTypeを拡張する
 */
class ProductListOrderByTypeExtension extends AbstractMasterTypeExtension
{
    public function getExtendedType()
    {
        return ProductListOrderByType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield ProductListOrderByType::class;
    }

}

