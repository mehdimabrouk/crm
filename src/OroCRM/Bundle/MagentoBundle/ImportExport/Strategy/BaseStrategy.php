<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnitOfWork;

use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;

abstract class BaseStrategy implements StrategyInterface, ContextAwareInterface
{
    /** @var ImportStrategyHelper */
    protected $strategyHelper;

    /** @var ContextInterface */
    protected $importExportContext;

    /**
     * @param ImportStrategyHelper $strategyHelper
     */
    public function __construct(ImportStrategyHelper $strategyHelper)
    {
        $this->strategyHelper = $strategyHelper;
    }

    /**
     * @param ContextInterface $importExportContext
     */
    public function setImportExportContext(ContextInterface $importExportContext)
    {
        $this->importExportContext = $importExportContext;
    }

    /**
     * @param mixed  $entity
     * @param string $entityName
     * @param string|array $criteria
     * @param array  $excludedProperties
     * @return mixed
     */
    protected function findAndReplaceEntity($entity, $entityName, $criteria = 'id', $excludedProperties = [])
    {
        if (is_array($criteria)) {
            $existingEntity = $this->getEntityByCriteria($criteria, $entity);
        } else {
            $existingEntity = $this->getEntityOrNull($entity, $criteria, $entityName);

        }

        if ($existingEntity) {
            $this->strategyHelper->importEntity($existingEntity, $entity, $excludedProperties);
            $entity = $existingEntity;
        } else {
            $entity->setId(null);
        }

        return $entity;
    }

    /**
     * @param object $entity
     * @return null|object
     */
    protected function validateAndUpdateContext($entity)
    {
        // validate contact
        $validationErrors = $this->strategyHelper->validateEntity($entity);
        if ($validationErrors) {
            $this->importExportContext->incrementErrorEntriesCount();
            $this->strategyHelper->addValidationErrors($validationErrors, $this->importExportContext);
            return null;
        }

        // increment context counter
        if ($entity->getId()) {
            $this->importExportContext->incrementReplaceCount();
        } else {
            $this->importExportContext->incrementAddCount();
        }

        return $entity;
    }

    /**
     * @param mixed $entity
     * @param string $entityIdField
     * @param string $entityClass
     * @return object|null
     */
    protected function getEntityOrNull($entity, $entityIdField, $entityClass)
    {
        $existingEntity = null;
        $entityId = $entity->{'get'.ucfirst($entityIdField)}();

        if ($entityId) {
            $existingEntity = $this->getEntityByCriteria([$entityIdField => $entityId], $entityClass);
        }

        return $existingEntity ?: null;
    }

    /**
     * @param array $criteria
     * @param object|string $entity object to get class from or class name
     *
     * @return object
     */
    protected function getEntityByCriteria(array $criteria, $entity)
    {
        if (is_object($entity)) {
            $entityClass = ClassUtils::getClass($entity);
        } else {
            $entityClass = $entity;
        }

        return $this->getEntityRepository($entityClass)->findOneBy($criteria);
    }

    /**
     * @param $entityName
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager($entityName)
    {
        return $this->strategyHelper->getEntityManager($entityName);
    }

    /**
     * @param string $entityName
     * @return EntityRepository
     */
    protected function getEntityRepository($entityName)
    {
        return $this->strategyHelper->getEntityManager($entityName)->getRepository($entityName);
    }

    /**
     * @param $entity
     *
     * @return object
     */
    protected function merge($entity)
    {
        $em = $this->getEntityManager(ClassUtils::getClass($entity));
        if ($em->getUnitOfWork()->getEntityState($entity) !== UnitOfWork::STATE_MANAGED) {
            $entity = $em->merge($entity);
        }

        return $entity;
    }
}
