<?php
//This checks for the existence of an always-existing PrestaShop constant (its version number), and if it does not exist, it stops the module from loading. 
//The sole purpose of this is to prevent malicious visitors to load this file directly.
if (!defined('_PS_VERSION_'))
{
  exit;
}

class ListaFatture extends Module
{

  public function __construct()
  {
    $this->name = 'listaFatture';
    $this->tab = 'front_office_features';
    $this->version = '1.0.0';
    $this->author = 'Antonio Gallucci';
    $this->need_instance = 0;
    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    $this->bootstrap = true;	

    parent::__construct();

    $this->displayName = $this->l('Lista Fatture');
    $this->description = $this->l('Gestisci le tue fatture da una pagina facile ed intiutiva.');

    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

    if (!Configuration::get('MYMODULE_NAME'))
      $this->warning = $this->l('No name provided');
  
	
	$this->tabs = array(
		array(
				'name' => 'Lista Fatture', // One name for all langs
				'class_name' => 'listaFatture',
				'visible' => true,
				'parent_class_name' => 'ShopParameters',
	));	  
  }

  public function install()
  {
    if (Shop::isFeatureActive())
      Shop::setContext(Shop::CONTEXT_ALL);

    if (!parent::install() ||
      !$this->registerHook('leftColumn') ||
      !$this->registerHook('header') ||
      !Configuration::updateValue('MYMODULE_NAME', 'my friend')
	  || !$this->installTab()
    )
      return false;

    return true;
  }  

  public function uninstall()
  {
    if (!parent::uninstall() ||
      !Configuration::deleteByName('MYMODULE_NAME')
	  || !$this->uninstallTab()
    )
      return false;

    return true;
  }

  public function getContent()
  {
      $output = null;
  
      if (Tools::isSubmit('submit'.$this->name))
      {
          $my_module_name = strval(Tools::getValue('MYMODULE_NAME'));
          if (!$my_module_name
            || empty($my_module_name)
            || !Validate::isGenericName($my_module_name))
              $output .= $this->displayError($this->l('Invalid Configuration value'));
          else
          {
              Configuration::updateValue('MYMODULE_NAME', $my_module_name);
              $output .= $this->displayConfirmation($this->l('Settings updated'));
          }
      }
      return $output.$this->displayForm();
  }

  public function displayForm()
  {
      // Get default language
      $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
  
      // Init Fields form array
      $fields_form[0]['form'] = array(
          'legend' => array(
              'title' => $this->l('Settings'),
          ),
          'input' => array(
              array(
                  'type' => 'text',
                  'label' => $this->l('Configuration value'),
                  'name' => 'MYMODULE_NAME',
                  'size' => 20,
                  'required' => true
              )
          ),
          'submit' => array(
              'title' => $this->l('Save'),
              'class' => 'btn btn-default pull-right'
          )
      );
  
      $helper = new HelperForm();
  
      // Module, token and currentIndex
      $helper->module = $this;
      $helper->name_controller = $this->name;
      $helper->token = Tools::getAdminTokenLite('AdminModules');
      $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
  
      // Language
      $helper->default_form_language = $default_lang;
      $helper->allow_employee_form_lang = $default_lang;
  
      // Title and toolbar
      $helper->title = $this->displayName;
      $helper->show_toolbar = true;        // false -> remove toolbar
      $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
      $helper->submit_action = 'submit'.$this->name;
      $helper->toolbar_btn = array(
          'save' =>
          array(
              'desc' => $this->l('Save'),
              'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
              '&token='.Tools::getAdminTokenLite('AdminModules'),
          ),
          'back' => array(
              'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
              'desc' => $this->l('Back to list')
          )
      );
  
      // Load current value
      $helper->fields_value['MYMODULE_NAME'] = Configuration::get('MYMODULE_NAME');
  
      return $helper->generateForm($fields_form);
  }  
  
     public function installTab()
     {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'ListaFatture';
		
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Lista Fatture';
        }


        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            //AdminPreferences
            $tab->id_parent = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)
                                ->getValue('SELECT MIN(id_tab)
											FROM `'._DB_PREFIX_.'tab`
											WHERE `class_name` = "'.pSQL('AdminParentOrders').'"'
                                        );
        } else {
            // AdminAdmin
            $tab->id_parent = (int)Tab::getIdFromClassName('AdminAdmin');
        }

        $tab->module = $this->name;
        return $tab->add();
     }

    public function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('ListaFatture');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        } else {
            return false;
        }
    }  
}