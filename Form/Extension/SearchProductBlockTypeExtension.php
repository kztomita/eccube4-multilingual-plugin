<?php

namespace Plugin\MultiLingual\Form\Extension;

use Eccube\Form\Type\SearchProductBlockType;
use Eccube\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * SearchProductBlockTypeを拡張する
 */
class SearchProductBlockTypeExtension extends AbstractTypeExtension
{
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $Categories = $this->categoryRepository
            ->getList(null, true);

        // category_id項目を上書き。
        // getLocaleNameWithLevel()でラベルを取得するようにする。
        $builder->add('category_id', EntityType::class, [
            'class' => 'Eccube\Entity\Category',
            'choice_label' => 'LocaleNameWithLevel',
            'choices' => $Categories,
            'placeholder' => 'common.select__all_products',
            'required' => false,
        ]);
    }

    public function getExtendedType()
    {
        return SearchProductBlockType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield SearchProductBlockType::class;
    }

}

