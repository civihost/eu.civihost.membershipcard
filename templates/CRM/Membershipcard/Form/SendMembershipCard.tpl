<div id="moveContribution" class="crm-block crm-form-block crm-contribution-move-form-block">
  <div class="help">
    {capture assign=contactUrl}{crmURL p='/civicrm/contact/view' q="reset=1&cid=`$contactId`"}{/capture}
    <div class="crm-i fa-info-circle"></div> {ts 1=$currentContactName 2=$contactUrl}Invia la tessera a <a href="%2">%1</a>.{/ts}
  </div>

  {foreach from=$elementNames item=elementName}
    <div class="crm-section">
      <div class="label">{$form.$elementName.label}</div>
      <div class="content">{$form.$elementName.html}</div>
      <div class="clear"></div>
    </div>
  {/foreach}

  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
