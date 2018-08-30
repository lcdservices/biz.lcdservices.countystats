{*
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
*}
{* Search form and results for Fields *}
  {assign var="showBlock" value="'searchForm'"}
  {assign var="hideBlock" value="'searchForm_show'"}
  <div class="crm-block crm-form-block crm-custom-search-form-block">
    <div class="crm-accordion-wrapper crm-custom_search_form-accordion">
      <div class="crm-accordion-header crm-master-accordion-header">
        {ts}County Stats{/ts}
      </div><!-- /.crm-accordion-header -->
      <div class="crm-accordion-body">
        {strip}
          <table class="form-layout">
           <tr>
              <td class="font-size12pt" colspan="2">
                  {$form.contactType.label}&nbsp;&nbsp;{$form.contactType.html|crmAddClass:'medium'}
              </td>
              <td class="font-size12pt" colspan="2">
                  {$form.states.label}&nbsp;&nbsp;{$form.states.html|crmAddClass:'medium'}
              </td>
              <td class="font-size12pt" colspan="2">
                  {$form.displayAll.label}&nbsp;&nbsp;{$form.displayAll.html|crmAddClass:'medium'}
              </td>
            </tr>
            <tr>
              <td>
              {include file="CRM/common/formButtons.tpl" location="bottom"}
              <div class="crm-submit-buttons reset-advanced-search">
                <a href="{crmURL p='civicrm/custom/countystats' q='reset=1'}" id="resetSearch" class="crm-hover-button" title="{ts}Clear all search criteria{/ts}">
                  <i class="crm-i fa-undo"></i>
                  &nbsp;{ts}Reset Form{/ts}
                </a>
              </div>
            </td>
            </tr>
            </table>
        {/strip}
      </div><!-- /.crm-accordion-body -->
    </div><!-- /.crm-accordion-wrapper -->
  </div><!-- /.crm-form-block -->

  {if $rowsEmpty || $rows}
    <div class="crm-content-block">
    {if $rowsEmpty}
    <div class="crm-results-block crm-results-block-empty">
      There are no data matching your search criteria.
    </div>
    {/if}

    {if $rows}
      <div class="crm-results-block">
        {* Search request has returned 1 or more matching rows. *}
        {* This section handles form elements for action task select and submit *}
        {* This section displays the rows along and includes the paging controls *}
       

        {include file="CRM/Countystats/Form/Selector.tpl" context="Search"}
        {* END Actions/Results section *}
      </div>
    {/if}

    </div>
  {/if}
