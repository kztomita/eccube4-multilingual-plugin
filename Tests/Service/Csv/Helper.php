<?php

namespace Plugin\MultiLingual\Tests\Service\Csv;

use Eccube\Common\EccubeConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Eccube\Service\CsvImportService;

class Helper
{
   /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    public function __construct(ContainerInterface $container)
    {
        $this->eccubeConfig = $container->get(EccubeConfig::class);
    }

    public function createCsvFile(string $contents)
    {
        $tmp = tmpfile();
        fwrite($tmp, $contents);
        rewind($tmp);
        $meta = stream_get_meta_data($tmp);
        $file = new \SplFileObject($meta['uri']);
        return $file;
    }

    public function createCsvImporterService(string $contents)
    {
        $importerService = new CsvImportService(
            self::createCsvFile($contents),
            $this->eccubeConfig['eccube_csv_import_delimiter'],
            $this->eccubeConfig['eccube_csv_import_enclosure']
        );
        $importerService->setHeaderRowNumber(0);
        return $importerService;
    }

}
