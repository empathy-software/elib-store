


{if isset($errors) and sizeof($errors) > 0}
    <ul id="error">
        {foreach from=$errors item=error}
            <li>{$error}</li>
        {/foreach}
    </ul>
{/if}


<h2>Rename</h2>

<form method="post">
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" value="{$brand->name}" class="form-control" name="artist_alias" id="name">
    </div>
    <input type="hidden" name="id" value="{$brand->id}" />
    <button type="submit" class="btn btn-primary" name="save">Save</button>
    <button type="submit" class="btn btn-primary" name="cancel">Cancel</button>
</form>

