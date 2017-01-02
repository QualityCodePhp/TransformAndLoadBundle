<?php

namespace QualityCode\TransformAndLoadBundle\Services;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use QualityCode\TransformAndLoadBundle\Services\Import\SchemaDefinitionGroupByEntity;
use QualityCode\TransformAndLoadBundle\Services\Import\GetDataFromCsvFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use QualityCode\TransformAndLoadBundle\Import\Transformer\TransformerInterface;
use QualityCode\TransformAndLoadBundle\Import\Transformer\TransformerFactory;

class Import
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TransformerFactory
     */
    private $transformerFactory;

    /**
     * @var SchemaDefinitionGroupByEntity
     */
    private $schemaDefintionToSchemaGroupByEntityConverter;

    /**
     * @var array
     */
    private $schemaDefinition;

    /**
     * @var array
     */
    private $schemaDefintiontGroupByEntity;

    /**
     * @var GetDataFromCsvFile
     */
    private $dataGetterFromCsv;

    /**
     * @var array
     */
    private $data;

    /**
     * @var int
     */
    private $dataSize;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @param EntityManagerInterface        $entityManager
     * @param SchemaDefinitionGroupByEntity $schemaDefintionToSchemaGroupByEntity
     * @param GetDataFromCsvFile            $dataGetterFromCsv
     * @param TransformerFactory            $transformerFactory
     */
    public function __construct(EntityManagerInterface $entityManager, SchemaDefinitionGroupByEntity $schemaDefintionToSchemaGroupByEntity, GetDataFromCsvFile $dataGetterFromCsv, TransformerFactory $transformerFactory)
    {
        $this->entityManager = $entityManager;
        $this->schemaDefintionToSchemaGroupByEntityConverter = $schemaDefintionToSchemaGroupByEntity;
        $this->dataGetterFromCsv = $dataGetterFromCsv;
        $this->transformerFactory = $transformerFactory;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param string $fileName
     * @param string $delimiter
     *
     * @return Import
     */
    protected function setData(string $fileName, string $delimiter)
    {
        $this->data = $this->dataGetterFromCsv->getData($fileName, $delimiter);
        $this->dataSize = sizeof($this->data);

        return $this;
    }

    /**
     * @param array $schemaDefinition
     *
     * @return Import
     */
    protected function setSchemaDefinition(array $schemaDefinition)
    {
        $this->schemaDefintiontGroupByEntity = $this->schemaDefintionToSchemaGroupByEntityConverter->convert($schemaDefinition);
        $this->schemaDefinition = $schemaDefinition;

        return $this;
    }

    /**
     * @param array           $schemaDefinition
     * @param string          $fileName
     * @param OutputInterface $output
     */
    public function import(array $schemaDefinition, string $fileName, OutputInterface $output)
    {
        $this->setSchemaDefinition($schemaDefinition);
        $this->setData($fileName, $this->schemaDefinition['delimiter']);
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        $i = 1;

        $this->initProgressBar($output);
        foreach ($this->data as $lineNumber => $line) {
            $fieldsGroupByEntity = $this->groupFieldsByEntity($line);
            $mainEntity = $this->linkEntities($this->buildEntities($fieldsGroupByEntity));
            $this->persistEntities($mainEntity, $i);
            $this->advanceProgressBar($i++);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @param array $entities
     *
     * @return mixed
     */
    protected function linkEntities(array $entities)
    {
        $mainEntity = $entities[$this->schemaDefinition['main_entity']];
        unset($entities[$this->schemaDefinition['main_entity']]);
        foreach ($entities as $entityName => $entity) {
            $fieldName = $this->schemaDefinition['link_entities_mapping'][$entityName];
            $this->propertyAccessor->setValue($mainEntity, $fieldName, $entity);
        }

        return $mainEntity;
    }

    /**
     * @param mixed $entity
     * @param int   $advance
     *
     * @return Import
     */
    protected function persistEntities($entity, int $advance)
    {
        $this->entityManager->persist($entity);
        if (($advance % $this->schemaDefinition['batch_size']) === 0) {
            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        return $this;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function buildEntities(array $fields)
    {
        $entities = array();

        foreach ($fields as $entityClassName => $values) {
            $entities[$entityClassName] = $this->setEntityFields($values, new $entityClassName());
        }

        return $entities;
    }

    /**
     * @param array $values
     * @param mixed $entity
     *
     * @return mixed
     */
    protected function setEntityFields(array $values, $entity)
    {
        foreach ($values as $name => $value) {
            $this->propertyAccessor->setValue($entity, $name, $value);
        }

        return $entity;
    }

    /**
     * @param array $line
     *
     * @return array
     */
    protected function groupFieldsByEntity(array $line)
    {
        $fieldsGroupByEntity = $this->initFieldsGroupByEntity();

        foreach ($this->schemaDefintiontGroupByEntity as $entityName => $fields) {
            foreach ($fields as $name => $config) {
                if (array_key_exists($name, $line)) {
                    $value = $this->getCellValue($config, $line[$name]);
                    $fieldsGroupByEntity[$entityName][$config['mapped_with']] = $value;
                }
            }
        }

        return $fieldsGroupByEntity;
    }

    /**
     * @param array  $config
     * @param string $value
     *
     * @return mixed
     */
    protected function getCellValue(array $config, string $value)
    {
        if (!array_key_exists('transform', $config)) {
            return $value;
        }

        $transformer = $this->getTransformer($config['transform']);
        if (null === $transformer) {
            return $value;
        }

        return $transformer->transform($value);
    }

    /**
     * @param string $transformerName
     *
     * @return TransformerInterface
     */
    protected function getTransformer(string $transformerName)
    {
        if (!array_key_exists($transformerName, $this->schemaDefinition['transformer'])) {
            return null;
        }

        return $this->transformerFactory->create($this->schemaDefinition['transformer'][$transformerName]);
    }

    /**
     * @return array
     */
    protected function initFieldsGroupByEntity()
    {
        $fieldsGroupByEntity = array();
        $keys = array_keys($this->schemaDefintiontGroupByEntity);

        foreach ($keys as $key) {
            $fieldsGroupByEntity[$key] = array();
        }

        return $fieldsGroupByEntity;
    }

    /**
     * @param OutputInterface $output
     *
     * @return \QualityCode\TransformAndLoadBundle\Services\Import
     */
    protected function initProgressBar(OutputInterface $output)
    {
        $this->progressBar = new ProgressBar($output, $this->dataSize);
        $this->progressBar->start();

        return $this;
    }

    /**
     * @return Import
     */
    protected function finishProgressBar()
    {
        $this->progressBar->finish();

        return $this;
    }

    /**
     * @param int $advance
     *
     * @return Import
     */
    protected function advanceProgressBar(int $advance)
    {
        if (($advance % $this->schemaDefinition['batch_size']) === 0) {
            $this->progressBar->advance($this->schemaDefinition['batch_size']);
        }

        return $this;
    }
}
