<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2018                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */
require_once 'CRM/Countystats/Selector/Search.php';
/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2018
 */

/**
 * Advanced search, extends basic search.
 */
class CRM_Countystats_Form_Search extends CRM_Core_Form_Search {

  /**
   * The params that are sent to the query.
   *
   * @var array
   */
  protected $_queryParams;

  /**
   * Are we restricting ourselves to a single contact.
   *
   * @var boolean
   */
  protected $_limit = NULL;
  
  /**
   * Processing needed for buildForm and later.
   */
  public function preProcess() {
    $this->set('searchFormName', 'Find County Stats');

    $this->_searchButtonName = $this->getButtonName('refresh');
    $this->_actionButtonName = $this->getButtonName('next', 'action');

    $this->_done = FALSE;
    // @todo - is this an error - $this->_defaults is used.
    $this->defaults = array();

    /*
     * we allow the controller to set force/reset externally, useful when we are being
     * driven by the wizard framework
     */

    $this->_reset = CRM_Utils_Request::retrieve('reset', 'Boolean');
    $this->_force = CRM_Utils_Request::retrieve('force', 'Boolean', $this, FALSE);
    $this->_limit = CRM_Utils_Request::retrieve('limit', 'Positive', $this);
    $this->_context = CRM_Utils_Request::retrieve('context', 'String', $this, FALSE, 'search');

    $this->assign("context", $this->_context);
    
    $sortID = NULL;
    if ($this->get(CRM_Utils_Sort::SORT_ID)) {
      $sortID = CRM_Utils_Sort::sortIDValue($this->get(CRM_Utils_Sort::SORT_ID),
        $this->get(CRM_Utils_Sort::SORT_DIRECTION)
      );
    }

    // get user submitted values
    // get it from controller only if form has been submitted, else preProcess has set this
    $this->_formValues = $this->getVar('_submitValues');

    $this->_queryParams = $this->_formValues;
    $selector = new CRM_Countystats_Selector_Search($this->_queryParams,
      $this->_action,
      $this->_limit,
      $this->_context
    );
    $prefix = NULL;
    
    $this->assign("{$prefix}limit", $this->_limit);
    
    $controller = new CRM_Core_Selector_Controller($selector,
      $this->get(CRM_Utils_Pager::PAGE_ID),
      $sortID,
      CRM_Core_Action::VIEW,
      $this,
      CRM_Core_Selector_Controller::TRANSFER,
      $prefix
    );
    $controller->setEmbedded(TRUE);
    $controller->moveFromSessionToTemplate();
    $query = &$selector->getQuery();
    $controller->run(); 
  }

  /**
   * Set defaults.
   *
   * @return array
   */
  public function setDefaultValues() {
    return $this->_defaults;
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    parent::buildQuickForm();
    
    $contactTypes = civicrm_api3('ContactType', 'get', array(
      'sequential' => 1,
    ));
    $contactTypeList = array();
    if( !empty($contactTypes['values']) ){
      foreach($contactTypes['values'] as $contactType){
        $contacttypeName = CRM_Utils_Array::value('name', $contactType);
        $contactTypeList[$contacttypeName] = CRM_Utils_Array::value('label', $contactType);
      }
    }
    $this->add('select', 'contactType', ts('Contact Type'), $contactTypeList);
    $states = new CRM_Core_PseudoConstant;
    $state_list = $states->stateProvince();
    $this->add('select', 'states', ts('Select State'), $state_list, TRUE);
    
    $this->add('advcheckbox', 'displayAll', ts('Display all counties'));
  }
 
  /**
   * The post processing of the form gets done here.
   *
   * Key things done during post processing are
   *      - check for reset or next request. if present, skip post processing.
   *      - now check if user requested running a saved search, if so, then
   *        the form values associated with the saved search are used for searching.
   *      - if user has done a submit with new values the regular post submission is
   *        done.
   * The processing consists of using a Selector / Controller framework for getting the
   * search results.
   */
  public function postProcess() {
    if ($this->_done) {
      return;
    }

    $this->_done = TRUE;

    if (!empty($_POST)) {
      $this->_formValues = $this->controller->exportValues($this->_name);
    }
    $this->_queryParams = $this->_formValues;

    $this->set('formValues', $this->_formValues);
    $this->set('queryParams', $this->_queryParams);

    $buttonName = $this->controller->getButtonName();
  
    $sortID = NULL;
    if ($this->get(CRM_Utils_Sort::SORT_ID)) {
      $sortID = CRM_Utils_Sort::sortIDValue($this->get(CRM_Utils_Sort::SORT_ID),
        $this->get(CRM_Utils_Sort::SORT_DIRECTION)
      );
    }
    
    $this->_queryParams = $this->_formValues;
    $selector = new CRM_Countystats_Selector_Search($this->_queryParams,
      $this->_action,
      $this->_limit,
      $this->_context
    );
    $selector->setKey($this->controller->_key);

    $prefix = NULL;
    $controller = new CRM_Core_Selector_Controller($selector,
      $this->get(CRM_Utils_Pager::PAGE_ID),
      $sortID,
      CRM_Core_Action::VIEW,
      $this,
      CRM_Core_Selector_Controller::SESSION,
      $prefix
    );
    $controller->setEmbedded(TRUE);
    $query = &$selector->getQuery();
    $controller->run();
  }

  /**
   * Return a descriptive name for the page, used in wizard header.
   *
   * @return string
   */
  public function getTitle() {
    return ts('County Stats');
  }
}
