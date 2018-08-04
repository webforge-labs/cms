<?php

namespace Webforge\Doctrine\Annotations;

use stdClass;
use Doctrine\ORM\Mapping\Annotation;
use ReflectionObject;
use Webforge\Types\Type;
use ReflectionClass;

/**
 *
 * wir müssen uns in dieser Klasse mit 2 Typen von Annotations rumschlagen:
 *  - \Psc\Code\Annotation hier alias PscAnnotation
 *  - Doctrine\ORM\Mapping\Annotation alias DoctrineAnnotation
 *
 *  warum das so ist liegt daran, dass ich die DoctrineAnnotation-Implementierung nicht mag (public properties, constructorInjection usw)
 *  Diese ist einfach nur dafür da, dass die Annotation korrekt ihre Values exportiert - was wir bei Doctrine Dirty mit Reflection machen müssen (bis alle DoctrineException mal Meta-Daten haben, wer weis)
 */
class Writer
{
    protected $annotationNamespaceAliases = array();
  
    protected $defaultAnnotationNamespace;
  
    /**
     * Annotation     ::= "@" AnnotationName ["(" [Values] ")"]
     *
     * AnnotationName ::= QualifiedName | SimpleName
     * QualifiedName  ::= NameSpacePart "\" {NameSpacePart "\"}* SimpleName
     * NameSpacePart  ::= identifier | null | false | true
     * SimpleName     ::= identifier | null | false | true
     *
     * @param mixed $annotation can be a Doctrine\ORM\Mapping\Annotation or a class which docblock contains @Annotation
     */
    public function writeAnnotation($annotation)
    {
        $writtenValues = array();
    
        foreach ($this->extractValues($annotation) as $key => $value) {
            $writtenValues[] = $this->writePropertyValue($key, $value);
        }

        $writtenAnnotation = '@'.$this->writeAnnotationName($annotation);

        if (count($writtenValues) > 0) {
            $writtenAnnotation .= sprintf('(%s)', implode(', ', $writtenValues));
        }

        return $writtenAnnotation;
    }

    private function writePropertyValue($key, $value)
    {
        if (is_string($key)) {
            return sprintf('%s=%s', $key, $this->writeValue($value));
        } else {
            return $this->writeValue($value);
        }
    }

    private function writeValue($value)
    {
        if ($value instanceof Annotation) {
            return $this->writeAnnotation($value);
        } elseif (is_object($value) && $this->isAnnotationClass(get_class($value))) {
            return $this->writeAnnotation($value);
        } elseif (is_string($value)) {
            return sprintf('"%s"', $value);
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_array($value)) {
            return $this->writeArray($value);
        } elseif (is_numeric($value)) {
            return $value;
        }

        throw new \RuntimeException('Unknown case for value: '.gettype($value));
    }

    private function isAnnotationClass($fqn)
    {
        $reflection = new ReflectionClass($fqn);

        return mb_strpos($reflection->getDocComment(), '@Annotation') !== false;
    }

    private function writeArray(array $values)
    {
        $writtenValues = array();

        foreach ($values as $key => $value) {
            $writtenValues[] = $this->writeKeyValue($key, $value);
        }

        return '{'.implode(", ", $writtenValues).'}';
    }
  
    private function writeKeyValue($key, $value)
    {
        if (is_string($key)) {
            return sprintf('"%s"=%s', $key, $this->writeValue($value));
        } else {
            return $this->writeValue($value);
        }
    }

    /**
     * Returns the writable Name for an annotation in the current context
     *
     * returns:
     *  - fqcn (with \\ prefixed) for not known annotation-namespaces
     *  - just the classname for anontations in the defaultAnnotationNamespace
     *  - the name with an alias (alias\className) for namespaces with an alias
     *
     * @param mixed $annotation can be a Doctrine\ORM\Mapping\Annotation or a class which docblock contains @Annotation
     * @return string
     */
    private function writeAnnotationName($annotation)
    {
        $name = get_class($annotation);
    
        if (isset($this->defaultAnnotationNamespace) && mb_strpos($name, $this->defaultAnnotationNamespace) === 0) {
            return mb_substr($name, mb_strlen($this->defaultAnnotationNamespace)+1);
        }
    
        foreach ($this->annotationNamespaceAliases as $alias => $namespace) {
            if (mb_strpos($name, $namespace) === 0) {
                return $alias.'\\'.mb_substr($name, mb_strlen($namespace)+1);
            }
        }
    
        return '\\'.$name;
    }
  
    private function extractValues($annotation)
    {
        $extractedValues = array();
      
        $annotationReflection = new ReflectionObject($annotation);
        foreach ($annotationReflection->getProperties() as $property) {
            $propertyValue = $property->getValue($annotation);
            $propertyName = $property->getName();
      
            if ($propertyName === 'value' && $propertyValue !== null) {
                $extractedValues[] = $propertyValue;
            } elseif (!$this->isSkippedDefaultValue($propertyValue, $propertyName, $property->getDeclaringClass()->getDefaultProperties())) {
                $extractedValues[$propertyName] = $propertyValue;
            }
        }
    
        return $extractedValues;
    }

    private function isSkippedDefaultValue($propertyValue, $propertyName, array $defaultProperties)
    {
        return array_key_exists($propertyName, $defaultProperties) && $defaultProperties[$propertyName] === $propertyValue;
    }
  
    public function setAnnotationNamespaceAlias($namespace, $alias)
    {
        $this->annotationNamespaceAliases[$alias] = $namespace;
        return $this;
    }
  
    public function setDefaultAnnotationNamespace($namespace)
    {
        $this->defaultAnnotationNamespace = $namespace;
        return $this;
    }
}
