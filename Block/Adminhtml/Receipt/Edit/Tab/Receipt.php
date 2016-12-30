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
namespace MindArc\Inventory\Block\Adminhtml\Receipt\Edit\Tab;

class Receipt extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Catalog product model factory
     *
     * @var Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * Stock item factory
     *
     * @var Magento\Directory\Model\Config\Source\CountryFactory
     */
    protected $_optionCountryFactory;

    /**
     * Stock item factory
     *
     * @var Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * Stock item factory
     *
     * @var Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\CatalogInventory\Api\Data\_optionCountryFactory $optionCountryFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Directory\Model\Config\Source\CountryFactory $optionCountryFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_formFactory = $formFactory;
        $this->_orderFactory = $orderFactory;
        $this->_optionCountryFactory = $optionCountryFactory;
        $this->_countryFactory = $countryFactory;
        $this->_regionFactory = $regionFactory;
        parent::__construct($context, $registry, $formFactory);
    }
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \MindArc\Inventory\Model\Receipt $receipt */
        $receipt = $this->_coreRegistry->registry('mindarc_inventory_receipt');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('receipt_');
        $form->setFieldNameSuffix('receipt');
        $generalFieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Receipt Information'),
                'class'  => 'fieldset-wide'
            ]
        );

        $extraFieldset = $form->addFieldset('extra_info', array(
            'legend'  => __('Extra Information'),
            'class'  => 'fieldset-wide'
        ));

        // Prep the form data
        $data = array();
        if ($receipt) {
            $data+= $receipt->getData();
            if ( !empty($data) && $data['reference_type'] == 'shipment' ) {
                $data['shipment_box_count']  = $receipt->getExtraInfo('box_count');
                $data['shipment_street1']    = $receipt->getExtraInfo('street',0);
                $data['shipment_street2']    = $receipt->getExtraInfo('street',1);
                $data['shipment_city']       = $receipt->getExtraInfo('city');
                $data['shipment_country_id'] = $receipt->getExtraInfo('country_id');
                $data['shipment_region_id']  = $receipt->getExtraInfo('region_id');
                $data['shipment_postcode']   = $receipt->getExtraInfo('postcode');
            }
            if ( !empty($data) && $data['reference_type'] == 'production' ) {
                $data['production_cost']     = $receipt->getExtraInfo('cost');
            }
            // for returned_item s display the increment id for the particular order
            if ( !empty($data) && $data['reference_type'] == 'returned_item' && !empty($data['reference_id'])) {
                $order = $this->_orderFactory->create()->load($data['reference_id']);
                if($order->getId()){
                    $data['reference_id'] = $order->getIncrementId();
                }
            }
        }

        if ($receipt->getId()) {
            $generalFieldset->addField(
                'receipt_id',
                'hidden',
                ['name' => 'receipt_id']
            );
        }
       // Add fields to the receipt fieldset
        $generalFieldset->addField('reference_type', 'select', array(
            'label'     => __('Type'),
            'name'      => 'reference_type',
            'options'   => $this->getReferenceTypes(),
            'disabled'  => !$this->canEdit(),
            'note'   => __('Do not use <em>%s</em> when you have already issued a credit memo for that order! (i.e. - CASE conferences)', __('Loaned Item Return'))
        ));

        if ($this->canEdit())
        $generalFieldset->addField('reference_id', ($this->canEdit() ? 'text' : 'label'), array(
                'label'     => __('Reference Number'),
                'name'      => 'reference_id',
                // lookup order button
                'after_element_html'=> '<button id="lookupOrderButton" type="button" style="margin:5px 0;" class="form-button" onclick="window.open(\''
                                        .$this->getUrl('mindarc_inventory/receipt/select',array())
                                        .'\',\'\',\'\');"><span>'
                                        .__('Lookup Order')
                                        .'</span></button>'
            ));

        /*$generalFieldset->addField('lookup_button', 'button' , array(
            'name'      => 'lookup_button',
            'value'     => __('### Number')
        )); */

        $generalFieldset->addField('name', ($this->canEdit() ? 'text' : 'label'), array(
            'label'     => __('Name'),
            'name'      => 'name'
        ));

        $generalFieldset->addField('comment', ($this->canEdit() ? 'textarea' : 'label'), array(
            'label'     => __('Comment'),
            'name'      => 'comment'
        ));

        // Add fields for production
        if ( $this->canEdit() || $data['reference_type'] == 'production' ) {
            $extraFieldset->addField('production_cost', ($this->canEdit() ? 'text' : 'label'), array(
                'label'     => __('Production Cost'),
                'display' => 'none',
                'name'      => 'production[cost]'
            ),'reference_type');
        }

        // Add fields for shipment
        if ( $this->canEdit() || $data['reference_type'] == 'shipment' ) {
            $extraFieldset->addField('shipment_box_count', ($this->canEdit() ? 'text' : 'label'), array(
                'label'     => __('No. of Boxes'),
                'display' => 'none',
                'name'      => 'shipment[box_count]'
            ));

            $extraFieldset->addField('shipment_street1', ($this->canEdit() ? 'text' : 'label'), array(
                'label'     => __('Street'),
                'display' => 'none',
                'name'      => 'shipment[street][0]'
            ));

            $extraFieldset->addField('shipment_street2', ($this->canEdit() ? 'text' : 'label'), array(
                'label'     => ' ',
                'display' => 'none',
                'name'      => 'shipment[street][1]'
            ));

            $extraFieldset->addField('shipment_city', ($this->canEdit() ? 'text' : 'label'), array(
                'label'     => __('City'),
                'display' => 'none',
                'name'      => 'shipment[city]'
            ));

            if ( $this->canEdit() ) {
                $extraFieldset->addField('shipment_country_id', 'select', array(
                    'label'     => __('Country'),
                    'class'     => 'countries',
                    'display' => 'none',
                    'name'      => 'shipment[country_id]',
                    'values'    => $this->_optionCountryFactory->create()->toOptionArray()
                ));

                $extraFieldset->addField('shipment_region_id', ($this->canEdit() ? 'text' : 'label'), array(
                    'label'     => __('State/Province'),
                    'display' => 'none',
                    'name'      => 'shipment[region_id]'
                ));
            } else {
                /* @var $country Mage_Directory_Model_Country */
                $country = $this->_countryFactory->create()->load($data['shipment_country_id']);
                $data['shipment_country_name'] = $country->getName();

                /* @var $region Mage_Directory_Model_Region */
                if ( is_numeric($data['shipment_region_id']) ) {
                    $region = $this->_regionFactory->create()->load($data['shipment_region_id']);
                } else {
                    $region = new \Magento\Framework\DataObject(array(
                        'name' => $data['shipment_region_id']
                    ));
                }
                $data['shipment_region_name'] = $region->getName();

                $extraFieldset->addField('shipment_country_name', 'label', array(
                    'display' => 'none',
                    'label'     => __('Country')
                ));

                $extraFieldset->addField('shipment_region_name', 'label', array(
                    'display' => 'none',
                    'label'     => __('State/Province'),
                ));
            }

            $extraFieldset->addField('shipment_postcode', ($this->canEdit() ? 'text' : 'label'), array(
                'label'     => __('Zip'),
                'display' => 'none',
                'name'      => 'shipment[postcode]'
            ));
        }

        // Set dependencies for fields that are specific to shipment or production
        if ( $this->canEdit() ) {
            $this->setChild('form_after', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
                ->addFieldMap("receipt_reference_type",      'reference_type')
                ->addFieldMap("receipt_shipment_box_count",  'shipment[box_count]')
                ->addFieldMap("receipt_shipment_street1",    'shipment[street][0]')
                ->addFieldMap("receipt_shipment_street2",    'shipment[street][1]')
                ->addFieldMap("receipt_shipment_city",       'shipment[city]')
                ->addFieldMap("receipt_shipment_country_id", 'shipment[country_id]')
                ->addFieldMap("receipt_shipment_region_id",  'shipment[region_id]')
                ->addFieldMap("receipt_shipment_postcode",   'shipment[postcode]')
                ->addFieldMap("receipt_production_cost",     'production[cost]')
                // Shipment fields
                ->addFieldDependence('shipment[box_count]',   'reference_type', 'shipment')
                ->addFieldDependence('shipment[street][0]',   'reference_type', 'shipment')
                ->addFieldDependence('shipment[street][1]',   'reference_type', 'shipment')
                ->addFieldDependence('shipment[city]' ,      'reference_type', 'shipment')
                ->addFieldDependence('shipment[country_id]', 'reference_type', 'shipment')
                ->addFieldDependence('shipment[region_id]',  'reference_type', 'shipment')
                ->addFieldDependence('shipment[postcode]',   'reference_type', 'shipment')
                // Production fields
                ->addFieldDependence('production[cost]',     'reference_type', 'production')
            );
        }


        $receiptData = $this->_session->getData('mindarc_inventory_receipt_data', true);
        if ($receiptData) {
            $receipt->addData($receiptData);
            $data = array_merge($data, $receiptData);

            // Reformat shipment info
            if ( !empty($data['shipment']) ) {
                $data['shipment_box_count']  = $data['shipment']['box_count'];
                $data['shipment_street1']    = $data['shipment']['street'][0];
                $data['shipment_street2']    = $data['shipment']['street'][1];
                $data['shipment_city']       = $data['shipment']['city'];
                $data['shipment_country_id'] = $data['shipment']['country_id'];
                $data['shipment_region_id']  = $data['shipment']['region_id'];
                $data['shipment_postcode']   = $data['shipment']['postcode'];
                unset($data['shipment']);
            }

            // Reformat production info
            if ( !empty($data['production']) ) {
                $data['production_cost']     = $data['production']['cost'];
                unset($data['production']);
            }

        } else {
            if (!$receipt->getId()) {
                $receipt->addData($receipt->getDefaultValues());
            }
        }
        $form->addValues($data);
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
        return __('Receipt');
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

    public function canEdit()
    {
        $receipt = $this->_coreRegistry->registry('mindarc_inventory_receipt');
        return (boolean)!$receipt->getId() && $this->_authorization->isAllowed('MindArc_Inventory::receipt_create');
        // return true;
    }

    public function getReferenceTypes()
    {
        return array(
            'shipment' => __('Shipment'),
            'production' => __('Print Job'),
            'returned_item' => __('Loaned Item Return')
        );
    }
}
