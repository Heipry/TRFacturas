<?php


/**
 * Print invoices by parameters
 * @category invoices
 *
 * @author Javier Diaz
 * @copyright Javier Diaz / PrestaShop
 * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
 * @version 0.4
 */
class AdminTRFacturasController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;

        $this->meta_title = 'Facturas TapasRioja';
        parent::__construct();
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }       
    }

    public function renderView()
    {

        return $this->renderConfigurationForm();

    }

    public function renderConfigurationForm()    
    {
       
        $invoiceList=$this->getInvoicesModels();
        $statesList=$this->getStates();
        $this->fields_value['states[]'] = explode(',',$obj->states);
       
        $inputs = array(  
            array(
                'type' => 'radio',
                'label' => $this->module->l('Export all?', 'AdminTRFacturas'),
                'hint' => $this->module->l('Yes = no filter status', 'AdminTRFacturas'),
                'name' => 'export_active',
                'values' => array(
                    array('id' => 'active_off', 'value' => 0, 'label' => $this->module->l('No, only selected', 'AdminTRFacturas')),
                    array('id' => 'active_on', 'value' => 1, 'label' => $this->module->l('Yes, all invoices', 'AdminTRFacturas')),
                ),
                'is_bool' => true,
            ),
            array(
                'type' => 'select',
                'label' => $this->module->l('Status', 'AdminTRFacturas'),                
                'desc' => $this->module->l('Ctrl for multiple status', 'AdminTRFacturas'),
                'name' => 'states[]',
                'multiple' => true,
                'options' => array(
                    'query' => $statesList,
                    'id' => 'value',
                    'name' => 'name'
                ),
            ),
            array(
                'type' => 'select',
                'label' => $this->module->l('Invoice model.','AdminTRFacturas'),                
                'desc' => $this->module->l('Choose an invoice model.', 'AdminTRFacturas'),
                'name' => 'invoiceM',
                'required' => true,
                'options' => array(
                    'query' => $invoiceList,
                    'id' => 'value',
                    'name' => 'name'
                ),
            ),
            
            array(
                'type' => 'date',
                'label' => $this->module->l('From', 'AdminTRFacturas'),
                'name' => 'date_from',
                'maxlength' => 10,
                'required' => true,
                'desc' => $this->module->l('Choose first day of interval', 'AdminTRFacturas'),
                'hint' => $this->module->l('Format: 2011-12-31 (inclusive).', 'AdminTRFacturas')
            ),
            array(
                'type' => 'date',
                'label' => $this->module->l('To', 'AdminTRFacturas'),
                'name' => 'date_to',
                'maxlength' => 10,
                'required' => true,
                'desc' => $this->module->l('Choose last day of interval', 'AdminTRFacturas'),
                'hint' => $this->module->l('Format: 2012-12-31 (inclusive).', 'AdminTRFacturas')
            )
            
        );
    

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('Print invoices', 'AdminTRFacturas'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->module->l('Print', 'AdminTRFacturas'),
                )
            ),
        );
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitGenerar';
        $helper->currentIndex = self::$currentIndex;
        $helper->token = Tools::getAdminTokenLite('AdminTRFacturas');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }


    public function getConfigFieldsValues()
    {
        $today = getdate();
        return array(
            'export_active' => Configuration::get("trfact_export_active"),
            //'states[]' => array(4,5,19,23,25,35,55),
            
            'states[]' => unserialize(Configuration::get("trfact_export_SELECTED_STATUSES")),
            'invoiceM' => Configuration::get("trfact_export_INVOICE"),
            'date_from' => $today,
            'date_to' => $today
        );
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitGenerar')) {
            $id_lang = Tools::getValue('export_language');
            $id_shop = (int)$this->context->shop->id;
            $states = (Tools::getValue('export_active')) ? 0 : Tools::getValue('states');
                if (!Validate::isDate(Tools::getValue('date_from')))
                $this->errors[] = $this->module->l('Invalid "From" date', 'AdminTRFacturas');
                if (!Validate::isDate(Tools::getValue('date_to')))
                $this->errors[] = $this->module->l('Invalid "To" date', 'AdminTRFacturas');

                if (!count($this->errors))
                {
                if (count(OrderInvoice::getByDateInterval(Tools::getValue('date_from'), Tools::getValue('date_to'))))
                {
                    $this->GenerateInvoicesPDF($states);
                }
                    $this->errors[] = $this->module->l('No invoice has been found for this period.','AdminTRFacturas');
                }
                if (count($this->errors))  print_r($this->errors);
         
            die();
        }
    }
public function GenerateInvoicesPDF($ids)
    {
        $fechainicio = (Tools::getValue('date_from'));
        $fechafin = (Tools::getValue('date_to'));
        $order_invoice_collection = array();
        $invoiceModel= (Tools::getValue('invoiceM'));
        
        $order_invoice_collection = $this->getByStatusDate($ids, $fechainicio,$fechafin);

        if (count($order_invoice_collection !=0)) {            
                $this->generatePDF($order_invoice_collection, $invoiceModel);
        }else   
        {    $a=0;
            var_dump($a);
             $this->errors[] = $this->module->l('No invoice has been found for this period.','AdminTRFacturas');
        }

    }

public function getByStatusDate($ids, $date_from, $date_to)

    {  
        if ($ids!=0) {
            $id='AND (o.current_state ='.$ids[0];
            for ($i=1; $i < count($ids); $i++) { 
                $id=$id.' OR o.current_state = '.$ids[$i];
            }
            $id=$id.') ';
        }else{
            $id = '';
        }
        

        $order_invoice_list = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT oi.*
            FROM `'._DB_PREFIX_.'order_invoice` oi
            LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = oi.`id_order`)
            WHERE DATE_ADD(oi.date_add, INTERVAL -1 DAY) <= \''.pSQL($date_to).'\'
            AND oi.date_add >= \''.pSQL($date_from).'\'
            '.$id.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'o').'
            AND oi.number > 0
            ORDER BY oi.number ASC
        ');

        return ObjectModel::hydrateCollection('OrderInvoice', $order_invoice_list);
    }

    public function initContent()
    {
        $this->content = $this->renderView();
        parent::initContent();
    }

public function generatePDF($object, $template,$orientation = 'P', $sinMargenes = false)
    {
        $pdf = new PDF($object, $template, Context::getContext()->smarty,$orientation);
        $pdf->render(true,0);
    }
public function getInvoicesModels()
    {
        $models = array();

        $templates_override = $this->getInvoicesModelsFromDir(_PS_THEME_DIR_.'pdf/');
        $templates_default = $this->getInvoicesModelsFromDir(_PS_PDF_DIR_);
      
        foreach (array_merge($templates_default, $templates_override) as $template)
        {
            $template_name = ucfirst(basename($template, '.tpl'));
            $models[] = array('value' => $template_name, 'name' => $template_name);
        }
        return $models;
    }
protected function getInvoicesModelsFromDir($directory)
    {
        $templates = false;

        if (is_dir($directory))
            $templates = glob($directory.'invoice*.tpl');

        if (!$templates)
            $templates = array();

        return $templates;
    }
public function getStates()
    {
        $models = array(
            
        );
        $states = new OrderState();
        $states2 = $states->getOrderStates($this->context->language->id);
 
        foreach (array_merge($states2) as $state)
        {
            $value = $state[id_order_state];
            $name = $state[name];
            $models[] = array('value' => $value, 'name' => $name);
        }
        
        return $models;
    }
    
}
