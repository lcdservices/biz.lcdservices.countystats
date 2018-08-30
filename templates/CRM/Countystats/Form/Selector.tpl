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
{include file="CRM/common/pager.tpl" location="top"}

{if $rows}
    <div class="crm-content-block crm-block">
    <div id="custom_group">
     {strip}
   {* handle enable/disable actions*}
   {include file="CRM/common/enableDisableApi.tpl"}
      <table id="options" class="row-highlight">
        <thead>
          <tr>
            
            {foreach from=$columnHeaders item=header}
              <th scope="col">
                {if $header.sort}
                  {assign var='key' value=$header.sort}
                  {$sort->_response.$key.link}
                {else}
                  {$header.name}
                {/if}
              </th>
            {/foreach}
      
          </tr>
        </thead>
        <tbody>
        {foreach from=$rows item=row}
        <tr id="CustomGroup-{$row.id}" data-action="setvalue" class="crm-entity {cycle values="odd-row,even-row"} {$row.class}">
          <td>{$row.name}</td>
          <td>{$row.state_name}</td>
          <td>{$row.c_count}</td>
        </tr>
        {/foreach}
        </tbody>
      </table>

        {/strip}
    </div>
    </div>
    {else}
       <div class="messages status no-popup">
       <img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"/> &nbsp;
         {capture assign=crmURL}{crmURL p='civicrm/admin/custom/group' q='action=add&reset=1'}{/capture}
         {ts 1=$crmURL}Data not found for selected criteria.{/ts}
       </div>
    {/if}
    
{include file="CRM/common/pager.tpl" location="bottom"}
{crmScript file='js/crm.expandRow.js'}
