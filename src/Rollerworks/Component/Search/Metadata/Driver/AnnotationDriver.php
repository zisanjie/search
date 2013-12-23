<?php

/*
 * This file is part of the Rollerworks Search Component package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Component\Search\Metadata\Driver;

use Doctrine\Common\Annotations\Reader;
use Metadata\Driver\DriverInterface;
use Metadata\MergeableClassMetadata;
use Rollerworks\Component\Search\Metadata\Field;
use Rollerworks\Component\Search\Metadata\PropertyMetadata;

/**
 * AnnotationDriver.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class AnnotationDriver implements DriverInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * Constructor.
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param \ReflectionClass $class
     * @param boolean          $test  Don't use this parameter, its only used for testing
     *
     * @return MergeableClassMetadata|null
     */
    public function loadMetadataForClass(\ReflectionClass $class, $test = false)
    {
        $classMetadata = new MergeableClassMetadata($class->name);

        if ($test) {
            $classMetadata->reflection = null;
            $classMetadata->createdAt = null;
        }

        $hasMetadata = false;

        foreach ($class->getProperties() as $reflectionProperty) {
            $annotation = $this->reader->getPropertyAnnotation($reflectionProperty, 'Rollerworks\Component\Search\Metadata\Field');

            if (null !== $annotation) {
                /** @var Field $annotation */
                $propertyMetadata = new PropertyMetadata($class->name, $reflectionProperty->name);
                $propertyMetadata->fieldName = $annotation->getName();
                $propertyMetadata->required = $annotation->isRequired();
                $propertyMetadata->type = $annotation->getType();
                $propertyMetadata->options = $annotation->getOptions();

                if ($test) {
                    $propertyMetadata->reflection = null;
                }

                $classMetadata->addPropertyMetadata($propertyMetadata);
                $hasMetadata = true;
            }
        }

        if (!$hasMetadata) {
            return null;
        }

        return $classMetadata;
    }
}
