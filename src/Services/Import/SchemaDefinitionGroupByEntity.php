<?php

namespace QualityCode\TransformAndLoadBundle\Services\Import;

class SchemaDefinitionGroupByEntity
{
    protected $schemaDefinitionByEntity = array();

    /**
     * @param array $schemaDefinition
     *
     * @return array
     */
    public function convert(array $schemaDefinition)
    {
        $this->schemaDefinitionByEntity[$schemaDefinition['main_entity']] = array();

        foreach ($schemaDefinition['fields'] as $key => $values) {
            $className = $this->getCurrentClassName($schemaDefinition['main_entity'], $values);
            $this->schemaDefinitionByEntity[$className][$key] = $this->getCleanValues($values);
        }

        return $this->schemaDefinitionByEntity;
    }

    /**
     * @param array $values
     *
     * @return array
     */
    protected function getCleanValues(array $values)
    {
        unset($values['class']);
        unset($values['link_entity']);

        return $values;
    }

    /**
     * @param string $mainClassName
     * @param array  $value
     *
     * @return string
     */
    protected function getCurrentClassName(string $mainClassName, array $value)
    {
        if (array_key_exists('link_entity', $value) && $value['link_entity'] === true) {
            return $this->checkIfAClassIsPresentInTheSchemaDefinition($value['class']);
        }

        return $mainClassName;
    }

    /**
     * @param string $className
     *
     * @return string
     */
    protected function checkIfAClassIsPresentInTheSchemaDefinition(string $className)
    {
        if (!array_key_exists($className, $this->schemaDefinitionByEntity)) {
            $this->schemaDefinitionByEntity[$className] = array();
        }

        return $className;
    }
}
