<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class TrFacturas extends Module
{
    public function __construct()
    {
    	$this->name = 'trfacturas';
        $this->tab = 'billing_invoicing';
        $this->version = '0.1';
        $this->author = 'Javier Diaz';
        $this->ps_versions_complianzy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = 'Facturas by TapasRioja';
        $this->description = 'Impresion de listado de facturas con parametros';
    }
    public function install() {
        $this->installController('AdminTRFacturas', 'TrFacturas');
		return parent::install();
    }
    public function uninstall()
	{
        $this->uninstallController('AdminTRFacturas');
        Configuration::deleteByName('trfact_export_active');
        Configuration::deleteByName('trfact_export_INVOICE');
        Configuration::deleteByName('trfact_export_SELECTED_STATUSES');

		return parent::uninstall();
    }
    public function getContent(){
        $states = new OrderState();
        $states2 = $states->getOrderStates($this->context->language->id);
        if (Tools::isSubmit("trfacturas_conf_form")) {
            Configuration::updateValue('trfact_export_active', Tools::getValue('export_active'));
            Configuration::updateValue('trfact_export_INVOICE', Tools::getValue('invoiceS'));
            foreach (array_merge($states2) as $state)
            {
                $value = $state[id_order_state];
                $actual_status = 'status'.$value;
                $active = Tools::getValue($actual_status);
                if ($active=='1') {
                    $statusId[] = $value;
                }
            }
            Configuration::updateValue('trfact_export_SELECTED_STATUSES', serialize($statusId));
        }
        $this->context->smarty->assign("export_active", Configuration::get("trfact_export_active"));
        $this->context->smarty->assign("invoice_active", Configuration::get("trfact_export_INVOICE"));
        $status_active = unserialize(Configuration::get("trfact_export_SELECTED_STATUSES"));
        $active = array();
        foreach (array_merge($states2) as $state)
        {
            $value = $state[id_order_state];
            $name = $state[name];            
            $statusID[] = $value;
            $statusName[] = $name;

            if (in_array($value,$status_active) ) {
                $active[] = '1';
                
            }else $active[] = '0';
        }
        require_once __DIR__ . '/controllers/admin/AdminTRFacturas.php';
        $controller = new AdminTRFacturasController();
        $invoices = $controller->getInvoicesModels();
       
        $this->context->smarty->assign("statusName", $statusName);
        $this->context->smarty->assign("statusID", $statusID);
        $this->context->smarty->assign("statusActive", $active);
        $this->context->smarty->assign("invoicenames", $invoices);
        return $this->display(__FILE__, "getContent.tpl");

    }
	private function installController($controllerName, $name) {
        $tab_admin_order_id = Tab::getIdFromClassName ('AdminTools') ? Tab::getIdFromClassName ('AdminTools') : Tab::getIdFromClassName ('AdminAdvancedParameters');
        $tab = new Tab();
        $tab->class_name = $controllerName;
        $tab->id_parent = $tab_admin_order_id;
        $tab->module = $this->name;
        $languages = Language::getLanguages(false);
        foreach($languages as $lang){
            $tab->name[$lang['id_lang']] = $name;
        }
    	$tab->save();
	}
	public function uninstallController($controllerName) {
		$tab_controller_main_id = TabCore::getIdFromClassName($controllerName);
		$tab_controller_main = new Tab($tab_controller_main_id);
		$tab_controller_main->delete();
    }
    public function onClickOption($type, $href = false){
        $confirm_reset = $this->l('Module reset will erase predefined options, are you sure you want to reset it ?');
        $confirm_delete = $this->l('Confirm delete?');
        $matchtype = array(
            'reset' => "return confirm (' $confirm_reset ');",
            'delete' => "return confirm (' $confirm_delete ');"
        );
        if (isset($matchtype[$type])) {
            return $matchtype[$type];
        }
        return '';
    }
}
