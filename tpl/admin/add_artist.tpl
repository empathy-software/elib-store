
{include file="elib://admin/admin_header.tpl"}


<div class="grey" style="padding:0.5em;">

{if isset($errors) and sizeof($errors) > 0}
<ul id="error">
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<h2>Add New Artist</h2>

<form method="post">
 <div class="form-group">
  <label for="forename">Forename</label>
  <input type="text" value="{$artist->forename}" class="form-control" name="forename" id="forename">
 </div>
 <div class="form-group">
  <label for="surname">Surname</label>
  <input type="text" value="{$artist->surname}" class="form-control" id="surname" name="surname">
 </div>
 <input type="hidden" name="id" value="{$artist->id}" />
 <button type="submit" class="btn btn-primary" name="save">Save</button>
 <button type="submit" class="btn btn-primary" name="cancel">Cancel</button>
</form>


{include file="elib://admin/admin_footer.tpl"}