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
namespace MindArc\Inventory\Controller\Adminhtml\Transaction;

class Delete extends \MindArc\Inventory\Controller\Adminhtml\Transaction
{
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('transaction_id');
        if ($id) {
            $item_id = "";
            try {
                /** @var \MindArc\Inventory\Model\Transaction $transaction */
                $transaction = $this->transactionFactory->create();
                $transaction->load($id);
                $item_id = $transaction->getItem_id();
                $transaction->delete();
                $this->messageManager->addSuccess(__('The Transaction has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_mindarc_inventory_transaction_on_delete',
                    ['item_id' => $item_id, 'status' => 'success']
                );
                $resultRedirect->setPath('mindarc_inventory/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_mindarc_inventory_transaction_on_delete',
                    ['item_id' => $item_id, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('mindarc_inventory/*/edit', ['transaction_id' => $id]);
                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('Transaction to delete was not found.'));
        // go to grid
        $resultRedirect->setPath('mindarc_inventory/*/');
        return $resultRedirect;
    }
}
