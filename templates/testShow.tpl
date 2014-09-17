{* retrieve activity with API *}
{crmAPI var='result' entity='Activity' action='getsingle' q='civicrm/ajax/rest' sequential=1 id=$activityID}

<div class="crm-block crm-content-block crm-case-activity-view-block">
  <table id="pum_case_activity_view" class="crm-info-panel">
    <tbody>
      {foreach from=$report.fields item=row}
        <tr class="crm-case-activity-view-{$row.label}">
          <td class="label">{$row.label}</td>
          {if $row.label eq 'Details'}
            {if $form.pumPrivacy.value eq 1}
              <td>{$row.value}</td>
            {else}
              <td>{$form.pumPrivacyText.value}</td>
            {/if}  
          {else}  
            <td>{$row.value}</td>
          {/if}  
        </tr>
      {/foreach}
    </tbody>
  </table>
</div>
