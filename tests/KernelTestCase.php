<?php

namespace QualityCode\TransformAndLoadBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;

abstract class KernelTestCase extends BaseKernelTestCase
{
    protected static function getKernelClass()
    {
        return 'QualityCode\TransformAndLoadBundle\Tests\App\AppKernel';
    }
}
