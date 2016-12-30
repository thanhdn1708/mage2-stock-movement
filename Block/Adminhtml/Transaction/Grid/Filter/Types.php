<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MindArc\Inventory\Block\Adminhtml\Transaction\Grid\Filter;

use Magento\Newsletter\Model\Queue;

/**
 * Adminhtml newsletter subscribers grid website filter
 */
class Types extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * @var array
     */
    protected static $_types;

    /**
     * @return void
     */
    protected function _construct()
    {
        self::$_types = [
            null => null,
            'manual'                 => __('Manual'),
            'order'                  => __('Order'),
            'order_creditmemo' => __('Credit Memo'),
            'stock_receipt' => __('Receipt')
        ];
        parent::_construct();
    }

    /**
     * @return array
     */
    protected function _getOptions()
    {
        $options = [];
        foreach (self::$_types as $status => $label) {
            $options[] = ['value' => $status, 'label' => __($label)];
        }

        return $options;
    }

    /**
     * @return array|null
     */
    public function getCondition()
    {
        return $this->getValue() === null ? null : ['eq' => $this->getValue()];
    }
}
