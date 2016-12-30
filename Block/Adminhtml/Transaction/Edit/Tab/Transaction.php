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
namespace MindArc\Inventory\Block\Adminhtml\Transaction\Edit\Tab;

class Transaction extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \MindArc\Inventory\Model\Transaction $transaction */
        $transaction = $this->_coreRegistry->registry('mindarc_inventory_transaction');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('transaction_');
        $form->setFieldNameSuffix('transaction');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Transaction Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        if ($transaction->getId()) {
            $fieldset->addField(
                'transaction_id',
                'hidden',
                ['name' => 'transaction_id']
            );
        }
        $fieldset->addField(
            'item_id',
            'text',
            [
                'name'  => 'item_id',
                'label' => __('Item Id'),
                'title' => __('Item Id'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'parent_type',
            'text',
            [
                'name'  => 'parent_type',
                'label' => __('Parent Type'),
                'title' => __('Parent Type'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'parent_id',
            'text',
            [
                'name'  => 'parent_id',
                'label' => __('Parent Id'),
                'title' => __('Parent Id'),
            ]
        );
        $fieldset->addField(
            'adjustment',
            'text',
            [
                'name'  => 'adjustment',
                'label' => __('Adjustment'),
                'title' => __('Adjustment'),
            ]
        );
        $fieldset->addField(
            'balance',
            'text',
            [
                'name'  => 'balance',
                'label' => __('Balance'),
                'title' => __('Balance'),
            ]
        );
        $fieldset->addField(
            'extra',
            'text',
            [
                'name'  => 'extra',
                'label' => __('Extra'),
                'title' => __('Extra'),
            ]
        );

        $transactionData = $this->_session->getData('mindarc_inventory_transaction_data', true);
        if ($transactionData) {
            $transaction->addData($transactionData);
        } else {
            if (!$transaction->getId()) {
                $transaction->addData($transaction->getDefaultValues());
            }
        }
        $form->addValues($transaction->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Transaction');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
