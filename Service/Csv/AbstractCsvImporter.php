<?php

namespace Plugin\MultiLingual\Service\Csv;

use Eccube\Service\CsvImportService;

abstract class AbstractCsvImporter
{
    /**
     * @var string[]
     */
    protected $errors = [];

    /**
     * @var int
     */
    protected $lineNo = 0;

    protected function addError(string $message)
    {
        $this->errors[] = $message;
    }

    protected function addErrorWithLineNo(string $message)
    {
        $this->errors[] = $this->lineNo . '行目:' . $message;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return count($this->errors) ? true : false;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * CSVファイルのインポート処理
     *
     * @param CsvImportService $importService
     * @return bool
     */
    abstract public function import(CsvImportService $importService): bool;

    abstract public function getCsvHeader(): array;
}
