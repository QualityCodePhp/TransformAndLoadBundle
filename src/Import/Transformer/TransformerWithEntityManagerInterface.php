<?php

namespace QualityCode\TransformAndLoadBundle\Import\Transformer;

interface TransformerWithEntityManagerInterface extends TransformerInterface
{
    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager();
}
