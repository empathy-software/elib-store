

{if isset($errors) and sizeof($errors) > 0}
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Error</strong>
        {foreach from=$errors item=e}
            <p>{$e}</p>
        {/foreach}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}
