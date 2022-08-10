<?php

namespace Plugin\MultiLingual\Form\Extension\Master;

use Eccube\Form\Type\Master\PrefType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Master\PrefTypeを拡張する
 */
class PrefTypeExtension extends AbstractMasterTypeExtension
{
    public function getExtendedType()
    {
        return PrefType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield PrefType::class;
    }

}

