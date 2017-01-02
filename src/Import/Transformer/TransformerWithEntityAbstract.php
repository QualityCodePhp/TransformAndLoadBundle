<?php

namespace QualityCode\TransformAndLoadBundle\Import\Transformer;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Description of TransformerWithEntityAbstract.
 *
 * @author fmetivier
 */
abstract class TransformerWithEntityAbstract
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
     * @return EntityManagerInterface
     */
    final public function getEntityManager()
    {
        return $this->entityManager;
    }
}
