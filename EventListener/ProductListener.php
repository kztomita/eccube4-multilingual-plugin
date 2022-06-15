<?php

namespace Plugin\MultiLingual\EventListener;

use Eccube\Common\EccubeConfig;
use Eccube\Entity\Product;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Plugin\MultiLingual\Entity\LocaleProduct;
use Plugin\MultiLingual\Repository\LocaleProductRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProductListener implements EventSubscriberInterface
{
    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LocaleProductRepository
     */
    protected $localeProductRepository;

    public function __construct(
        EccubeConfig            $eccubeConfig,
        EntityManagerInterface  $entityManager,
        LocaleProductRepository $localeProductRepository
    )
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->entityManager = $entityManager;
        $this->localeProductRepository = $localeProductRepository;
    }

    public static function getSubscribedEvents(): array
    {
        // LocaleProductの削除については、Productのcascade={"remove"}指定により、
        // Product削除時に自動で削除される。
        // ProductTrait参照。
        return [
            EccubeEvents::ADMIN_PRODUCT_EDIT_COMPLETE => 'onAdminProductEditComplete',
        ];
    }

    public function onAdminProductEditComplete(EventArgs $event): void
    {
        /** @var FormInterface $form */
        $form = $event->getArgument('form');

        /** @var Product $Product */
        $Product = $event->getArgument('Product');

        $locales = $this->eccubeConfig['multi_lingual_locales'];
        foreach ($locales as $locale) {
            $LocaleProduct = $this->localeProductRepository->findOneBy([
                'product_id' => $Product->getId(),
                'locale' => $locale,
            ]);
            if ($LocaleProduct) {
                continue;
            }
            // Product新規作成時はLocaleProductも作成
            $lp = new LocaleProduct;
            $lp->setName($Product->getName());
            $lp->setProductId($Product->getId());
            $lp->setProduct($Product);
            // DescriptionList,DescriptionDetailはnullにしておく
            $lp->setLocale($locale);
            $this->entityManager->persist($lp);
            $this->entityManager->flush();
        }
    }
}
