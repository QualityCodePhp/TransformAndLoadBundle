<?php

namespace QualityCode\TransformAndLoadBundle\Services\Import;

use QualityCode\TransformAndLoadBundle\Services\ConvertCsvToArray;

class GetDataFromCsvFile
{
    /**
     * @var ConvertCsvToArray
     */
    protected $csvToArrayConverter;

    /**
     * @param ConvertCsvToArray $csvToArrayConverter
     */
    public function __construct(ConvertCsvToArray $csvToArrayConverter)
    {
        $this->csvToArrayConverter = $csvToArrayConverter;
    }

    /**
     * @param string $fileName
     * @param string $delimiter
     *
     * @return array
     */
    public function getData(string $fileName, string $delimiter)
    {
        $data = $this->csvToArrayConverter->convert($fileName, $delimiter);

        return $data;
    }
}
