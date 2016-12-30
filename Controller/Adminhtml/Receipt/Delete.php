<?php
/**
 * MindArc_Inventory extension
 *                     NOTICE OF LICENSE
 * 
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 * 
 *                     @category  MindArc
 *                     @package   MindArc_Inventory
 *                     @copyright Copyright (c) 2016
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace MindArc\Inventory\Controller\Adminhtml\Receipt;

class Delete extends \MindArc\Inventory\Controller\Adminhtml\Receipt
{
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('receipt_id');
        if ($id) {
            $name = "";
            try {
                /** @var \MindArc\Inventory\Model\Receipt $receipt */
                $receipt = $this->receiptFactory->create();
                $receipt->load($id);
                $name = $receipt->getName();
                $receipt->delete();
                $this->messageManager->addSuccess(__('The Receipt has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_mindarc_inventory_receipt_on_delete',
                    ['name' => $name, 'status' => 'success']
                );
                $resultRedirect->setPath('mindarc_inventory/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_mindarc_inventory_receipt_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('mindarc_inventory/*/edit', ['receipt_id' => $id]);
                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('Receipt to delete was not found.'));
        // go to grid
        $resultRedirect->setPath('mindarc_inventory/*/');
        return $resultRedirect;
    }
}
