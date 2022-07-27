<?php

namespace Plugin\MultiLingual\Form\Extension\Master;

use Eccube\Form\Type\Master\SexType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Master\SexTypeを拡張する
 */
class SexTypeExtension extends AbstractMasterTypeExtension
{
    public function getExtendedType()
    {
        return SexType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield SexType::class;
    }

}

