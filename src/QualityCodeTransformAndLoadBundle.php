<?php

namespace QualityCode\TransformAndLoadBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use QualityCode\TransformAndLoadBundle\DependencyInjection\QualityCodeTransformAndLoadExtension;

class QualityCodeTransformAndLoadBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new QualityCodeTransformAndLoadExtension();
    }
}
