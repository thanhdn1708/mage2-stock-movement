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
namespace MindArc\Inventory\Block\Adminhtml\Item;

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
     * Initialize Item edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'item_id';
        $this->_blockGroup = 'MindArc_Inventory';
        $this->_controller = 'adminhtml_item';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Item'));
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
        $this->buttonList->remove('delete');
    }
    /**
     * Retrieve text for header element depending on loaded Item
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var \MindArc\Inventory\Model\Item $receipt */
        $receipt = $this->coreRegistry->registry('mindarc_inventory_item');
        if ($item->getId()) {
            return __("Edit item '%1'", $this->escapeHtml($item->getName()));
        }
        return __('New Item');
    }
}
