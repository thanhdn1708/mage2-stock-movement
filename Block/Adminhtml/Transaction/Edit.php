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
namespace MindArc\Inventory\Block\Adminhtml\Transaction;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * constructor
     * 
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize Transaction edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'transaction_id';
        $this->_blockGroup = 'MindArc_Inventory';
        $this->_controller = 'adminhtml_transaction';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Transaction'));
        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete Transaction'));
    }
    /**
     * Retrieve text for header element depending on loaded Transaction
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var \MindArc\Inventory\Model\Transaction $transaction */
        $transaction = $this->coreRegistry->registry('mindarc_inventory_transaction');
        if ($transaction->getId()) {
            return __("Edit Transaction '%1'", $this->escapeHtml($transaction->getItem_id()));
        }
        return __('New Transaction');
    }
}
