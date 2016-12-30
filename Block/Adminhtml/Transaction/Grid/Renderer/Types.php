<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MindArc\Inventory\Block\Adminhtml\Transaction\Grid\Renderer;

/**
 * Adminhtml newsletter queue grid block status item renderer
 */
class Types extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var array
     */
    protected static $_types;

    /**
     * Constructor for Grid Renderer Status
     *
     * @return void
     */
    protected function _construct()
    {
        self::$_types = [
            'manual'                 => __('Manual'),
            'order'                  => __('Order'),
            'order_creditmemo' => __('Credit Memo'),
            'stock_receipt' => __('Receipt')
        ];
        parent::_construct();
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return __($this->getTypes($row->getParentType()));
    }

    /**
     * @param string $status
     * @return \Magento\Framework\Phrase
     */
    public static function getTypes($types)
    {
        if (isset(self::$_types[$types])) {
            return self::$_types[$types];
        }

        return __('Unknown');
    }
}
