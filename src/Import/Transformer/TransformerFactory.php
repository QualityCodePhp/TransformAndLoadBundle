<?php

namespace QualityCode\TransformAndLoadBundle\Import\Transformer;

use Doctrine\ORM\EntityManagerInterface;

class TransformerFactory
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $transformerClassName
     *
     * @return TransformerInterface
     */
    public function create(string $transformerClassName)
    {
        if ($this->isWithEntityManager($transformerClassName)) {
            return new $transformerClassName($this->entityManager);
        }

        return new $transformerClassName();
    }

    /**
     * @param string $transformerClassName
     *
     * @return bool
     */
    protected function isWithEntityManager(string $transformerClassName)
    {
        $reflectionClass = new \ReflectionClass($transformerClassName);
        if ($reflectionClass->implementsInterface('QualityCode\TransformAndLoadBundle\Import\Transformer\TransformerWithEntityManagerInterface')) {
            return true;
        }

        if ($reflectionClass->isSubclassOf('QualityCode\TransformAndLoadBundle\Import\Transformer\TransformerWithEntityAbstract')) {
            return true;
        }

        return false;
    }
}
