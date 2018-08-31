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

/**
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2018
 */

/**
 * Class to render contribution search results.
 */
class CRM_Countystats_Selector_Search extends CRM_Core_Selector_Base implements CRM_Core_Selector_API {

  /**
   * We use desc to remind us what that column is, name is used in the tpl
   *
   * @var array
   */
  static $_columnHeaders;
  
  /**
   * Are we restricting ourselves to a single contact
   *
   * @var boolean
   */
  protected $_limit = NULL;
  
  /**
   * What context are we being invoked from
   *
   * @var string
   */
  protected $_context = NULL;

  /**
   * QueryParams is the array returned by exportValues called on
   * the HTML_QuickForm_Controller for that page.
   *
   * @var array
   */
  public $_queryParams;

  /**
   * Represent the type of selector
   *
   * @var int
   */
  protected $_action;

  /**
   * The query object
   *
   * @var string
   */
  protected $_query;

  /**
   * Class constructor.
   *
   * @param array $queryParams
   *   Array of parameters for query.
   * @param \const|int $action - action of search basic or advanced.
   * @param string $contributionClause
   *   If the caller wants to further restrict the search (used in contributions).
   * @param bool $single
   *   Are we dealing only with one contact?.
   * @param int $limit
   *   How many contributions do we want returned.
   *
   * @param string $context
   * @param null $compContext
   *
   * @return \CRM_Contribute_Selector_Search
   */
  public function __construct(
    &$queryParams,
    $action = CRM_Core_Action::NONE,
    $limit = NULL,
    $context = 'search'
  ) {
    // submitted form values
    $this->_queryParams = &$queryParams;
    $this->_limit = $limit;
    // type of selector
    $this->_action = $action;
    $params = array();
    $whereClause = $this->whereClause($params);
    $this->_params = $params;
    
    $sql = "SELECT cc.name as name, Count(ccont.id) as c_count,  csp.name as state_name FROM civicrm_county cc 
    LEFT JOIN civicrm_address ca ON cc.id = ca.county_id 
    LEFT JOIN civicrm_contact ccont ON ccont.id = ca.contact_id 
    LEFT JOIN civicrm_state_province csp ON csp.id = cc.state_province_id 
    WHERE $whereClause 
    GROUP BY cc.id";
    $this->_query = $sql;
  }

  /**
   * Getter for array of the parameters required for creating pager.
   *
   * @param $action
   * @param array $params
   */
  public function getPagerParams($action, &$pager_params) {
    $pager_params['status'] = ts('County') . ' %%StatusMessage%%';
    $pager_params['csvString'] = NULL;
    if ($this->_limit) {
      $pager_params['rowCount'] = $this->_limit;
    }
    else {
      $pager_params['rowCount'] = CRM_Utils_Pager::ROWCOUNT;
    }

    $pager_params['buttonTop'] = 'PagerTopButton';
    $pager_params['buttonBottom'] = 'PagerBottomButton';
  }

  /**
   * Returns total number of rows for the query.
   *
   * @param string $action
   *
   * @return int
   *   Total number of rows
   */
  public function getTotalCount($action) {
    $dao = CRM_Core_DAO::executeQuery($this->_query, $this->_params);
    return $dao->N;
  }

  /**
   * Returns all the rows in the given offset and rowCount.
   *
   * @param string $action
   *   The action being performed.
   * @param int $offset
   *   The row number to start from.
   * @param int $rowCount
   *   The number of rows to return.
   * @param string $sort
   *   The sql string that describes the sort order.
   * @param string $output
   *   What should the result set include (web/email/csv).
   *
   * @return int
   *   the total number of rows for this action
   */
  public function &getRows($action, $offset, $rowCount, $sort, $output = NULL) {
    if ($rowCount > 0 && $offset >= 0) {
      $offset = CRM_Utils_Type::escape($offset, 'Int');
      $rowCount = CRM_Utils_Type::escape($rowCount, 'Int');
      $this->_query .= " LIMIT $offset, $rowCount ";
    }
    $dao = CRM_Core_DAO::executeQuery($this->_query, $this->_params);
    $rows = array();
    while ($dao->fetch()) {
      $rows[] = array('name' => $dao->name, 'state_name' => $dao->state_name, 'c_count' => $dao->c_count);
    }
    return $rows;
  }

  /**
   * Returns the column headers as an array of tuples:
   * (name, sortName (key to the sort array))
   *
   * @param string $action
   *   The action being performed.
   * @param string $output
   *   What should the result set include (web/email/csv).
   *
   * @return array
   *   the column headers that need to be displayed
   */
  public function &getColumnHeaders($action = NULL, $output = NULL) {
    $pre = array();
    self::$_columnHeaders = array(
      array(
            'name' => ts('List County'),
            'field_name' => 'county',
          ),
           array(
            'name' => ts('State'),
            'field_name' => 'state',
          ),
          array(
            'name' => ts('Count'),
          ),
    );
    self::$_columnHeaders
      = array_merge(
        self::$_columnHeaders, array(
          array('desc' => ts('Actions'), 'type' => 'actions'),
        )
      );
    foreach (array_keys(self::$_columnHeaders) as $index) {
      // Add weight & space it out a bit to allow headers to be inserted.
      self::$_columnHeaders[$index]['weight'] = $index * 10;
    }

    return self::$_columnHeaders;
  }

  /**
   * @return mixed
   */
  public function alphabetQuery() {
    return $this->_query->searchQuery(NULL, NULL, NULL, FALSE, FALSE, TRUE);
  }

  /**
   * @return string
   */
  public function &getQuery() {
    return $this->_query;
  }

  /**
   * Name of export file.
   *
   * @param string $output
   *   Type of output.
   *
   * @return string
   *   name of the file
   */
  public function getExportFileName($output = 'csv') {
    return ts('CiviCRM County Stats');
  }
  /**
   * @param array $queryParams
   * @param bool $sortBy
   * @param $force
   *
   * @return string
   */
  public function whereClause(&$params) {
    $clauses = array();
    $contactType = CRM_Utils_Array::value('contactType', $this->_queryParams);   
    $states = CRM_Utils_Array::value('states', $this->_queryParams);   
    $displayAll = CRM_Utils_Array::value('displayAll', $this->_queryParams);   
    $params = array();
    if( $displayAll != 1){
      if ($contactType && !in_array("any", $contactType) ) {
        $typeClause = array();
        foreach ($contactType as $key => $type) {
          $types = explode('__', is_numeric($type) ? $key : $type, 2);    
          $ctype = $types[0];
          $contact_clauses = "( ccont.contact_type = '$ctype'";
          // Add sub-type if specified
          if (!empty($types[1])) { 
            $stype = $types[1];
            $contact_clauses .= " AND ccont.contact_sub_type LIKE '%" . CRM_Core_DAO::VALUE_SEPARATOR . $stype . CRM_Core_DAO::VALUE_SEPARATOR . "%'" . "";
          }
          $contact_clauses .= ")";
          $typeClause[] = $contact_clauses;
        }
        $clauses[] = "(".(!empty($typeClause) ? implode(' OR ', $typeClause) : '(1)').")";
      }
      else{
        $clauses[] = " ccont.contact_type IS NOT NULL";
      }
    }
    if ($states) {
      $clauses[] = " cc.state_province_id = '$states'";
    }
    return !empty($clauses) ? implode(' AND ', $clauses) : '(1)';
  }
}
