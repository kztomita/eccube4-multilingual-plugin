<?php

namespace Plugin\MultiLingual\Form\Extension\Master;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Master\ProductListOrderByType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Master\ProductListOrderByTypeを拡張する
 */
class ProductListOrderByExtension extends AbstractTypeExtension
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
        return ProductListOrderByType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield ProductListOrderByType::class;
    }

}

