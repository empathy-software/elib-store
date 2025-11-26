

{if isset($errors) and sizeof($errors) > 0}
    <div class="errors">
    {foreach from=$errors item=error}
        <div class="alert alert-danger" role="alert">
            {$error}
        </div>
    {/foreach}
    </div>
{/if}

