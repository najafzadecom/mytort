<?php
declare (strict_types=1);
namespace MailPoetVendor\Doctrine\ORM;
if (!defined('ABSPATH')) exit;
use MailPoetVendor\Doctrine\ORM\Mapping\ClassMetadata;
use InvalidArgumentException;
use function array_map;
use function count;
use function get_class;
use function gettype;
use function implode;
use function is_object;
use function method_exists;
use function reset;
use function spl_object_id;
use function sprintf;
class ORMInvalidArgumentException extends InvalidArgumentException
{
 public static function scheduleInsertForManagedEntity($entity)
 {
 return new self('A managed+dirty entity ' . self::objToStr($entity) . ' can not be scheduled for insertion.');
 }
 public static function scheduleInsertForRemovedEntity($entity)
 {
 return new self('Removed entity ' . self::objToStr($entity) . ' can not be scheduled for insertion.');
 }
 public static function scheduleInsertTwice($entity)
 {
 return new self('Entity ' . self::objToStr($entity) . ' can not be scheduled for insertion twice.');
 }
 public static function entityWithoutIdentity($className, $entity)
 {
 return new self("The given entity of type '" . $className . "' (" . self::objToStr($entity) . ') has no identity/no ' . 'id values set. It cannot be added to the identity map.');
 }
 public static function readOnlyRequiresManagedEntity($entity)
 {
 return new self('Only managed entities can be marked or checked as read only. But ' . self::objToStr($entity) . ' is not');
 }
 public static function newEntitiesFoundThroughRelationships($newEntitiesWithAssociations)
 {
 $errorMessages = array_map(static function (array $newEntityWithAssociation) : string {
 [$associationMapping, $entity] = $newEntityWithAssociation;
 return self::newEntityFoundThroughRelationshipMessage($associationMapping, $entity);
 }, $newEntitiesWithAssociations);
 if (count($errorMessages) === 1) {
 return new self(reset($errorMessages));
 }
 return new self('Multiple non-persisted new entities were found through the given association graph:' . "\n\n * " . implode("\n * ", $errorMessages));
 }
 public static function newEntityFoundThroughRelationship(array $associationMapping, $entry)
 {
 return new self(self::newEntityFoundThroughRelationshipMessage($associationMapping, $entry));
 }
 public static function detachedEntityFoundThroughRelationship(array $assoc, $entry)
 {
 return new self('A detached entity of type ' . $assoc['targetEntity'] . ' (' . self::objToStr($entry) . ') ' . " was found through the relationship '" . $assoc['sourceEntity'] . '#' . $assoc['fieldName'] . "' " . 'during cascading a persist operation.');
 }
 public static function entityNotManaged($entity)
 {
 return new self('Entity ' . self::objToStr($entity) . ' is not managed. An entity is managed if its fetched ' . 'from the database or registered as new through EntityManager#persist');
 }
 public static function entityHasNoIdentity($entity, $operation)
 {
 return new self('Entity has no identity, therefore ' . $operation . ' cannot be performed. ' . self::objToStr($entity));
 }
 public static function entityIsRemoved($entity, $operation)
 {
 return new self('Entity is removed, therefore ' . $operation . ' cannot be performed. ' . self::objToStr($entity));
 }
 public static function detachedEntityCannot($entity, $operation)
 {
 return new self('Detached entity ' . self::objToStr($entity) . ' cannot be ' . $operation);
 }
 public static function invalidObject($context, $given, $parameterIndex = 1)
 {
 return new self($context . ' expects parameter ' . $parameterIndex . ' to be an entity object, ' . gettype($given) . ' given.');
 }
 public static function invalidCompositeIdentifier()
 {
 return new self('Binding an entity with a composite primary key to a query is not supported. ' . 'You should split the parameter into the explicit fields and bind them separately.');
 }
 public static function invalidIdentifierBindingEntity()
 {
 return new self('Binding entities to query parameters only allowed for entities that have an identifier.');
 }
 public static function invalidAssociation(ClassMetadata $targetClass, $assoc, $actualValue)
 {
 $expectedType = $targetClass->getName();
 return new self(sprintf('Expected value of type "%s" for association field "%s#$%s", got "%s" instead.', $expectedType, $assoc['sourceEntity'], $assoc['fieldName'], is_object($actualValue) ? get_class($actualValue) : gettype($actualValue)));
 }
 public static function invalidEntityName($entityName)
 {
 return new self(sprintf('Entity name must be a string, %s given', gettype($entityName)));
 }
 private static function objToStr($obj) : string
 {
 return method_exists($obj, '__toString') ? (string) $obj : get_class($obj) . '@' . spl_object_id($obj);
 }
 private static function newEntityFoundThroughRelationshipMessage(array $associationMapping, $entity) : string
 {
 return 'A new entity was found through the relationship \'' . $associationMapping['sourceEntity'] . '#' . $associationMapping['fieldName'] . '\' that was not' . ' configured to cascade persist operations for entity: ' . self::objToStr($entity) . '.' . ' To solve this issue: Either explicitly call EntityManager#persist()' . ' on this unknown entity or configure cascade persist' . ' this association in the mapping for example @ManyToOne(..,cascade={"persist"}).' . (method_exists($entity, '__toString') ? '' : ' If you cannot find out which entity causes the problem implement \'' . $associationMapping['targetEntity'] . '#__toString()\' to get a clue.');
 }
}
