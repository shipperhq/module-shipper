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


namespace ShipperHQ\Shipper\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

abstract class Synchronize extends Action
{
    /**
     * Result page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Synchronizer factory
     *
     * @var  \ShipperHQ\Shipper\Model\Synchronizer
     */
    protected $sychronizerFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \ShipperHQ\Shipper\Model\SynchronizerFactory $synchronizerFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \ShipperHQ\Shipper\Model\SynchronizerFactory $synchronizerFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->sychronizerFactory = $synchronizerFactory;
    }

    /**
     * News access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ShipperHQ_Shipper::synchronize');
    }
}
