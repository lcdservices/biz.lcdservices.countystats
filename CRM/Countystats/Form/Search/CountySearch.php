<?php
use CRM_Countystats_ExtensionUtil as E;

/**
 * A custom contact search
 */
class CRM_Countystats_Form_Search_CountySearch extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
  function __construct(&$formValues) {
    parent::__construct($formValues);
  }

  /**
   * Prepare a set of search fields
   *
   * @param CRM_Core_Form $form modifiable
   * @return void
   */
  function buildForm(&$form) {
    CRM_Utils_System::setTitle(E::ts('County Stats'));

    $contactTypes = CRM_Contact_BAO_ContactType::getSelectElements();
    if ($contactTypes) {
      $form->add('select', 'contactType', ts('Contact Type(s)'), array('any' => 'Select') + $contactTypes, FALSE,
        array('id' => 'contact_type', 'multiple' => 'multiple', 'class' => 'crm-select2', 'style' => 'width: 100%;')
      );
    }
    
    $state_count = civicrm_api3('StateProvince', 'getcount', array('country_id' => 1228) );
    $params = array(
      'country_id' => 1228, //for United States only
      'options' => array(
        'limit' => $state_count,
      ),
    );
    $states = civicrm_api3('StateProvince', 'get', $params);
    $state_list = array();
    if( !empty($states['values']) ){
      foreach($states['values'] as $state){
        $stateID = CRM_Utils_Array::value('id', $state);
        $state_list[$stateID] = CRM_Utils_Array::value('name', $state);
      }
    }
    $form->add('select', 'states', ts('Select State'), $state_list, TRUE);
    
    $form->add('advcheckbox', 'displayAll', ts('Display all counties'));

    // Optionally define default search values
    $form->setDefaults(array(
      'contactType' => '',
      'states' => NULL,
      'displayAll' => NULL,
    ));

    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', array('contactType', 'states', 'displayAll'));
  }

  /**
   * Get a list of summary data points
   *
   * @return mixed; NULL or array with keys:
   *  - summary: string
   *  - total: numeric
   */
  function summary() {
    return NULL;
    // return array(
    //   'summary' => 'This is a summary',
    //   'total' => 50.0,
    // );
  }

  /**
   * Get a list of displayable columns
   *
   * @return array, keys are printable column headers and values are SQL column names
   */
  function &columns() {
    // return by reference
    $columns = array(
      E::ts('List County') => 'name',
      E::ts('State') => 'state_name',
      E::ts('Count') => 'c_count',
    );
    return $columns;
  }

  /**
   * Construct a full SQL query which returns one page worth of results
   *
   * @param int $offset
   * @param int $rowcount
   * @param null $sort
   * @param bool $includeContactIDs
   * @param bool $justIDs
   * @return string, sql
   */
  function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE) {
    $where = $this->where();
    $from = $this->from();
    $select = $this->select();
    $sql = "
    SELECT     $select $from
    WHERE      $where
    GROUP BY   cc.id
    ";
  return $sql;
  }

  /**
   * Construct a SQL SELECT clause
   *
   * @return string, sql fragment with SELECT arguments
   */
  function select() {
    return "
      cc.name              as name,
      Count(contact_a.id)  as c_count,
      state_province.name  as state_name
    ";
  }
 
  /**
   * Construct a SQL FROM clause
   *
   * @return string, sql fragment with FROM and JOIN clauses
   */
  function from() {
    return " FROM civicrm_county cc
      LEFT JOIN civicrm_address address ON cc.id = address.county_id
      LEFT JOIN civicrm_contact contact_a ON contact_a.id = address.contact_id 
      LEFT JOIN civicrm_state_province state_province ON state_province.id = cc.state_province_id
    ";
  }

  /**
   * Construct a SQL WHERE clause
   *
   * @param bool $includeContactIDs
   * @return string, sql fragment with conditional expressions
   */
  function where($includeContactIDs = FALSE) {
    $clauses = array();
    $where = "";
    $contactType = CRM_Utils_Array::value('contactType', $this->_formValues);   
    $states = CRM_Utils_Array::value('states', $this->_formValues);   
    $displayAll = CRM_Utils_Array::value('displayAll', $this->_formValues);   
    $params = array();
    if( $displayAll != 1){
      if ($contactType && !in_array("any", $contactType) ) {
        $typeClause = array();
        foreach ($contactType as $key => $type) {
          $types = explode('__', is_numeric($type) ? $key : $type, 2);    
          $ctype = $types[0];
          $contact_clauses = "( contact_a.contact_type = '$ctype'";
          // Add sub-type if specified
          if (!empty($types[1])) { 
            $stype = $types[1];
            $contact_clauses .= " AND contact_a.contact_sub_type LIKE '%" . CRM_Core_DAO::VALUE_SEPARATOR . $stype . CRM_Core_DAO::VALUE_SEPARATOR . "%'" . "";
          }
          $contact_clauses .= ")";
          $typeClause[] = $contact_clauses;
        }
        $clauses[] = "(".(!empty($typeClause) ? implode(' OR ', $typeClause) : '(1)').")";
      }
      else{
        $clauses[] = " contact_a.contact_type IS NOT NULL";
      }
    }
    else{
      if ($contactType && !in_array("any", $contactType) ) {
        $typeClause = array();
        foreach ($contactType as $key => $type) {
          $types = explode('__', is_numeric($type) ? $key : $type, 2);    
          $ctype = $types[0];
          $contact_clauses = "( contact_a.contact_type = '$ctype'";
          // Add sub-type if specified
          if (!empty($types[1])) { 
            $stype = $types[1];
            $contact_clauses .= " AND contact_a.contact_sub_type LIKE '%" . CRM_Core_DAO::VALUE_SEPARATOR . $stype . CRM_Core_DAO::VALUE_SEPARATOR . "%'" . "";
          }
          $contact_clauses .= ")";
          $typeClause[] = $contact_clauses;
        }
        $contact_clause[] = "(".(!empty($typeClause) ? implode(' OR ', $typeClause) : '(1)').")";
      }
      else{
        $contact_clause[] = " contact_a.contact_type IS NOT NULL";
      }
      $contact_clause[] = " address.contact_id IS NULL";
      
      $clauses[] = "(".(!empty($contact_clause) ? implode(' OR ', $contact_clause) : '(1)').")";
    }
    if ($states) {
      $clauses[] = " cc.state_province_id = '$states'";
    }
    
    if (!empty($clauses)) {
      $where .= !empty($clauses) ? implode(' AND ', $clauses) : '(1)';
    }
    return $this->whereClause($where, $params);
  }

  /**
   * Determine the Smarty template for the search screen
   *
   * @return string, template path (findable through Smarty template path)
   */
  function templateFile() {
    //return 'CRM/Contact/Form/Search/Custom.tpl';
  }

  /**
   * Modify the content of each row
   *
   * @param array $row modifiable SQL result row
   * @return void
   */
  function alterRow(&$row) {
    unset($row['action']);
  }
}
