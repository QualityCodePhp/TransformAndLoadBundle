<?php

namespace QualityCode\TransformAndLoadBundle\Tests\App;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use QualityCode\TransformAndLoadBundle\QualityCodeTransformAndLoadBundle;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new QualityCodeTransformAndLoadBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),

        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yml');
    }
}
