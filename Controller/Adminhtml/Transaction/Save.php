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

class Save extends \MindArc\Inventory\Controller\Adminhtml\Transaction
{
    /**
     * Backend session
     * 
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * constructor
     * 
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \MindArc\Inventory\Model\TransactionFactory $transactionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \MindArc\Inventory\Model\TransactionFactory $transactionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->backendSession = $backendSession;
        parent::__construct($transactionFactory, $registry, $resultRedirectFactory, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('transaction');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $transaction = $this->initTransaction();
            $transaction->setData($data);
            $this->_eventManager->dispatch(
                'mindarc_inventory_transaction_prepare_save',
                [
                    'transaction' => $transaction,
                    'request' => $this->getRequest()
                ]
            );
            try {
                $transaction->save();
                $this->messageManager->addSuccess(__('The Transaction has been saved.'));
                $this->backendSession->setMindArcInventoryTransactionData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'mindarc_inventory/*/edit',
                        [
                            'transaction_id' => $transaction->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('mindarc_inventory/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Transaction.'));
            }
            $this->_getSession()->setMindArcInventoryTransactionData($data);
            $resultRedirect->setPath(
                'mindarc_inventory/*/edit',
                [
                    'transaction_id' => $transaction->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('mindarc_inventory/*/');
        return $resultRedirect;
    }
}
