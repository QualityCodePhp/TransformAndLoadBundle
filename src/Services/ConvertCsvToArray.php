<?php

namespace QualityCode\TransformAndLoadBundle\Services;

class ConvertCsvToArray
{
    /**
     * @var array
     */
    private $header = null;

    /**
     * @var array
     */
    private $data = array();

    /**
     * @param string $fileName
     * @param string $delimiter
     *
     * @return array
     */
    public function convert(string $fileName, $delimiter = ',')
    {
        $handle = $this->getFileHandle($fileName);
        if ($handle === false) {
            return array();
        }

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $this->fillData($row);
        }
        fclose($handle);

        return $this->data;
    }

    /**
     * @param array $row
     *
     * @return ConvertCsvToArray
     */
    protected function fillData(array $row)
    {
        if (empty($this->header)) {
            $this->header = $row;
        } else {
            $this->data[] = array_combine($this->header, $row);
        }

        return $this;
    }

    /**
     * @param string $fileName
     *
     * @return bool|resource
     */
    private function getFileHandle(string $fileName)
    {
        if (!file_exists($fileName) || !is_readable($fileName)) {
            return false;
        }

        return fopen($fileName, 'r');
    }
}
