<?php
/**
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ\Shipper
 * @copyright Copyright (c) 2015 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */


namespace ShipperHQ\Shipper\Model\Order;

class Packages extends \Magento\Framework\Model\AbstractExtensibleModel
{
    /**
     * @var /ShipperHQ\Shipper\Model\ResourceModel\Order\Packages\Collection
     */
    private $orderPackageCollection;

    /**
     * @param \ShipperHQ\Shipper\Model\ResourceModel\Quote\Packages\CollectionFactory $orderPackageCollectionFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \ShipperHQ\Shipper\Model\ResourceModel\Order\Packages\CollectionFactory $orderPackageCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->orderPackageCollection = $orderPackageCollectionFactory->create();
    }

    /**
     * @param $orderId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByOrderId($orderId)
    {
        $collection = $this->orderPackageCollection
            ->addOrderIdToFilter($orderId);
        return $collection;
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('ShipperHQ\Shipper\Model\ResourceModel\Order\Packages');
    }
}
