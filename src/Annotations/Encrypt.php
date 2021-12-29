<?php

namespace Insitaction\FieldEncryptBundle\Annotations;

use Attribute;
use Doctrine\ORM\Mapping\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Encrypt implements Annotation, Attribute
{
}