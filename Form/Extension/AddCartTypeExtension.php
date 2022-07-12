<?php

namespace Plugin\MultiLingual\Form\Extension;

use Eccube\Form\Type\AddCartType;
use Eccube\Repository\CategoryRepository;
use Plugin\MultiLingual\Common\LocaleHelper;
use Plugin\MultiLingual\Repository\LocaleClassCategoryRepository;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * AddCartTypeを拡張する
 */
class AddCartTypeExtension extends AbstractTypeExtension
{
    /**
     * @var LocaleClassCategoryRepository
     */
    private $repository;

    public function __construct(LocaleClassCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var $Product \Eccube\Entity\Product */
        $Product = $options['product'];

        // classcategory_id1項目を上書き
        // 指定Localeの翻訳データがあれば差し替える
        if ($builder->has('classcategory_id1')) {
            $choices = [];
            foreach ($Product->getClassCategories1() as $id => $name) {
                $LocaleClassCategory = $this->repository->findOneBy([
                    'parent_id' => $id,
                    'locale' => LocaleHelper::getCurrentRequestLocale(),
                ]);
                if ($LocaleClassCategory) {
                    $name = $LocaleClassCategory->getName();
                }
                $choices[$name] = $id;
            }
            $builder->add('classcategory_id1', ChoiceType::class, [
                        'label' => $Product->getClassName1(),
                        'choices' => ['common.select' => '__unselected'] + $choices,
                        'mapped' => false,
                    ]);
        }

        // classcategory_id2の翻訳処理は
        // TwigExtension::translateClassCategoriesJson()で行う。
    }

    public function getExtendedType()
    {
        return AddCartType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield AddCartType::class;
    }
}
