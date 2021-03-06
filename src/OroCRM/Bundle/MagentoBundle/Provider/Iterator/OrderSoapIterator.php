<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;

use OroCRM\Bundle\MagentoBundle\Provider\Dependency\OrderDependencyManager;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class OrderSoapIterator extends AbstractPageableSoapIterator
{
    /**
     * {@inheritdoc}
     */
    public function getEntityIds()
    {
        $stores = [];
        if ($this->websiteId !== StoresSoapIterator::ALL_WEBSITES) {
            $stores = $this->getStoresByWebsiteId($this->websiteId);
        }
        $filters = $this->getBatchFilter($this->lastSyncDate, [], $stores);

        $result = $this->transport->call(SoapTransport::ACTION_ORDER_LIST, $filters);
        $result = $this->processCollectionResponse($result);

        $this->entityBuffer = array_combine(
            array_map(
                function ($item) {
                    if (is_object($item)) {
                        return $item->order_id;
                    } else {
                        return $item['order_id'];
                    }
                },
                $result
            ),
            $result
        );

        $idFieldName = $this->getIdFieldName();
        $result      = array_map(
            function ($item) use ($idFieldName) {
                $inc = is_object($item) ? $item->increment_id : $item['increment_id'];
                $id  = is_object($item) ? $item->order_id : $item['order_id'];

                return (object)['increment_id' => $inc, $idFieldName => $id];
            },
            $result
        );

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntity($id)
    {
        $id = $id->{$this->getIdFieldName()};

        if (!array_key_exists($id, $this->entityBuffer)) {
            $this->logger->warning(sprintf('Entity with id "%s" was not found', $id));

            return false;
        }

        $result = $this->entityBuffer[$id];
        $this->addDependencyData($result);

        return ConverterUtils::objectToArray($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function addDependencyData($result)
    {
        OrderDependencyManager::addDependencyData($result, $this->transport);
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdFieldName()
    {
        return 'order_id';
    }
}
