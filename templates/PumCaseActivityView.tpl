{* retrieve activity with API *}
{crmAPI var='result' entity='Activity' action='getsingle' q='civicrm/ajax/rest' sequential=1 id=$activityID}
{assign var=pumActivityTypeId value=$result.activity_type_id}
<div class="crm-block crm-content-block crm-case-activity-view-block">
  {if $pumActivityRedirect eq 1}
    <div class="messages status no-popup">
      <div class="icon inform-icon"></div>
      You are not authorised to edit this activity
    </div>
  {/if}
  <table class="form-layout-compressed">
    <tbody>
      {foreach from=$report.fields item=row}
        <tr class="crm-case-activity-view-{$row.label}">
          <td class="label">{$row.label}</td>
          {if $row.label eq 'Details'}
            {if in_array($pumActivityTypeId, $config->pumPrivacyActivityTypes)}
              {if $pumPrivacy eq 1}
                <td>{$row.value}</td>
              {else}
                <td>{$config->pumPrivacyText}</td>
              {/if}
            {else}
              <td>{$row.value}</td>              
            {/if}
          {else}  
            <td>{$row.value}</td>
          {/if}  
        </tr>
      {/foreach}
    </tbody>
  </table>
  {if $pumActivityRedirect eq 1}
  <div class="crm-submit-buttons">
    <a class="button" href="{$doneUrl}">Done</a>
  </div>
  {/if}  
</div>
