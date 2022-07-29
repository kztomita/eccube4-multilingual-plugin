<?php

namespace Plugin\MultiLingual\Repository;

use Eccube\Common\EccubeConfig;
use Eccube\Doctrine\Query\Queries;
use Eccube\Repository\ProductRepository;
use Eccube\Util\StringUtil;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ProductRepositoryDecorator extends ProductRepository
{
    /**
     * @var ProductRepository
     */
    private $inner;

    public function __construct(
        ProductRepository $inner,
        RegistryInterface $registry,
        Queries $queries,
        EccubeConfig $eccubeConfig
    )
    {
        $this->inner = $inner;

        parent::__construct($registry, $queries, $eccubeConfig);
    }

    /**
     * 日本語以外の商品名でも検索できるようにする
     */
    public function getQueryBuilderBySearchData($searchData)
    {
        $copied = $searchData;
        unset($copied['name']);

        $qb = $this->inner->getQueryBuilderBySearchData($copied);

        if (isset($searchData['name']) && StringUtil::isNotBlank($searchData['name'])) {
            $keywords = preg_split('/[\s　]+/u', str_replace(['%', '_'], ['\\%', '\\_'], $searchData['name']), -1, PREG_SPLIT_NO_EMPTY);

            foreach ($keywords as $index => $keyword) {
                $key = sprintf('keyword%s', $index);
                $qb
                    ->andWhere(sprintf('NORMALIZE(p.name) LIKE NORMALIZE(:%s) OR
                        NORMALIZE(p.search_word) LIKE NORMALIZE(:%s) OR
                        EXISTS (SELECT wpc%d FROM \Eccube\Entity\ProductClass wpc%d WHERE p = wpc%d.Product AND NORMALIZE(wpc%d.code) LIKE NORMALIZE(:%s)) OR
                        EXISTS (SELECT lp%d FROM \Plugin\MultiLingual\Entity\LocaleProduct lp%d WHERE p.id = lp%d.parent_id AND NORMALIZE(lp%d.name) LIKE NORMALIZE(:%s))',
                        $key, $key, $index, $index, $index, $index, $key,
                        $index, $index, $index, $index, $key))
                    ->setParameter($key, '%'.$keyword.'%');
            }
        }

        return $qb;
    }
}
