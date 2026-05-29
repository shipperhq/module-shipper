<?php
/**
 * ShipperHQ
 *
 * @category ShipperHQ
 * @package ShipperHQ\Shipper
 * @copyright Copyright (c) 2014 Zowta LTD and Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */

namespace ShipperHQ\Shipper\Controller\Adminhtml\Synchronize;

class Index extends \ShipperHQ\Shipper\Controller\Adminhtml\Synchronize
{
    /**
     * Index Action for Synchronize
     * @return Void
     * */

    public function execute()
    {
        $result = $this->sychronizerFactory->create()->updateSynchronizeData();
        if (array_key_exists('error', $result)) {
            $message = __($result['error']);
            $this->messageManager->addError($message);
        } elseif ($result['result'] == 0) {
            $message = __('Received latest attribute values from ShipperHQ, no changes are required.');
            $this->messageManager->addSuccess($message);
        } else {
            $message = __(
                'Received latest attribute values from ShipperHQ, %1 changes required. Ready to synchronize',
                $result['result']
            );
            $this->messageManager->addSuccess($message);
        }

        return $this->resultPageFactory->create();
    }
}
