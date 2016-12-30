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
namespace MindArc\Inventory\Block\Adminhtml\Receipt\Edit;

/**
 * @method Tabs setTitle(\string $title)
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $_jsonEncoder;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_authSession = $authSession;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->_jsonEncoder = $jsonEncoder;
    }
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('receipt_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Receipt Information'));
    }

    protected function _beforeToHtml()
    {
        $receipt = $this->_coreRegistry->registry('mindarc_inventory_receipt');

        if (!$receipt->getId())
        {
            $this->addTab('products', array(
                'label'     => __('Products'),
                'title'     => __('Products'),
                'url'       => $this->getUrl('mindarc_inventory/receipt/chooser',array(
                    'use_massaction' => true,
                    'receipt_id'     => $receipt->getId(),
                    'readonly'       => (bool) $receipt->getId())
                ),
                'class'     => 'ajax'
            ));
        }

        $activeTab = str_replace("{$this->getId()}_",'',$this->getRequest()->getParam('tab'));
        if ($activeTab) $this->setActiveTab($activeTab);

        return parent::_beforeToHtml();
    }
}
