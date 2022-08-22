<?php

namespace Plugin\MultiLingual\Form\Extension\Master;

use Eccube\Form\Type\Master\JobType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Master\JobTypeを拡張する
 */
class JobTypeExtension extends AbstractMasterTypeExtension
{
    public function getExtendedType()
    {
        return JobType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield JobType::class;
    }

}

