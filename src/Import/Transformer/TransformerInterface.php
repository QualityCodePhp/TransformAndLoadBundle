<?php

namespace QualityCode\TransformAndLoadBundle\Import\Transformer;

interface TransformerInterface
{
    /**
     * @param string $value
     *
     * @return mixed Value after tranformation
     */
    public function transform(string $value);
}
