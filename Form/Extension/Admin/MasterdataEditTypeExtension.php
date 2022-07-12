<?php

namespace Plugin\MultiLingual\Form\Extension\Admin;

use Doctrine\Common\Collections\Collection;
use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Admin\MasterdataEditType;
use Plugin\MultiLingual\Common\LocaleHelper;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Admin\MasterdataEditTypeを拡張する
 */
class MasterdataEditTypeExtension extends AbstractTypeExtension
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * CategoryType constructor.
     *
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(
        EccubeConfig $eccubeConfig,
        EntityManagerInterface $entityManager
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // 既存の'data'に項目を追加するので、$builderへの新規項目追加は行わない。
        // setData()しているデータのフォーマットは
        // Controller/Admin/Setting/System/MasterdataController参照。
        //
        // $data['data']は CollectionTypeでフォーマットはMasterdataDataTypeで
        // 定義されているので(MasterdataEditType参照)、
        // MasterdataDataExtensionで'name_' + localeの項目を追加している。

        // Localeに関する項目の表示切り替え用パラメータを渡すために
        // ダミーのfieldを追加する。
        $builder
            ->add('has_locale', TextType::class, [
                'mapped' => false,
            ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {

            $form = $event->getForm();

            $masterdataName = $form->get('masterdata_name')->getData();
            if ($masterdataName === null) {
                // マスターデータ種別未選択時
                return;
            }
            $entityName = str_replace('-', '\\', $masterdataName);

            // Traitで拡張したgetLocales()が存在するかチェック
            if (!LocaleHelper::hasLocaleFeature($entityName)) {
                // Localeの存在しないMasterデータ
                $form->get('has_locale')->setData('0');
                return;
            }

            $form->get('has_locale')->setData('1');

            $repository = $this->entityManager->getRepository($entityName);

            $data = $form->get('data')->getData();
            foreach ($data as &$entry) {
                /* $entry is ['id' => xx, 'name' => xxxx] */
                $id = intval($entry['id']);
                $masterdata = $repository->find($id);
                if (!$masterdata) {
                    continue;
                }
                /** @var Collection $localeEntities */
                $localeEntities = $masterdata->getLocales();
                foreach ($localeEntities as $Entity) {
                    $entry['name_' . $Entity->getLocale()] = $Entity->getName();
                }
            }
            // $dataはobject(Entity)ではなくarrayなのでsetData()で登録しなおす必要がある。
            $form->get('data')->setData($data);
        });
    }

    public function getExtendedType()
    {
        return MasterdataEditType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        yield MasterdataEditType::class;
    }

}

