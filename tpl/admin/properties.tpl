{include file="elib://admin/admin_header.tpl"}


{if $event neq 'rename'}
<div id="operations">
<div class="grey_top">
<div class="top_right">
<div class="top_left"></div>
</div>
</div>

<div class="grey" style="padding:0.5em;">

<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/properties/add" method="get">
<div><button type="submit" name="add" value="1">Add</button></div>
</form>


</div>
<div class="grey_bottom">
<div class="bottom_right">
<div class="bottom_left"></div>
</div>
</div>
</div>


<p style="line-height: 0.5em;">&nbsp;</p>
{/if}








<div class="grey_top">
<div class="top_right">
<div class="top_left"></div>
</div>
</div>

<div class="grey">



{if isset($errors) and sizeof($errors) > 0}
<ul id="error">
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}


{if $event eq 'rename'}
<fieldset><legend>Rename Property</legend>
<form action="" method="post">
<p><label>Name</label>
<input type="text" value="{$property->name}" name="name" />
</p>
<p><label>&nbsp;</label>
<button type="submit" name="save">Save</button>
</p>
</form>
</fieldset>

{else}
<div id="properties">

{foreach from=$properties key=id item=property}

<fieldset><legend>{$property.name}</legend>

<p style="margin: 0.5em;"><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/properties/rename/{$id}">Rename</a></p>

<form action="" method="post">

{if isset($property.option) and sizeof($property.option) > 0}
{foreach from=$property.option item=option key=option_id}
<p><label>&nbsp;</label>
<span><span class="option" id="option_{$option_id}">{$option}</span></span>
</p>
{/foreach}
{/if}

<p><label>&nbsp;</label>
<input type="text" value="{if $submitted_option->property_id eq $id}{$submitted_option->option_val}{/if}" name="option" />
<input type="hidden" name="id" value="{$id}" />
<button type="submit" name="add_option">Add</button>
</p>
</form>
</fieldset>
{/foreach}
</div>

{/if}


<p class="clear">&nbsp;</p>

</div>
<div class="grey_bottom">
<div class="bottom_right">
<div class="bottom_left"></div>
</div>
</div>






{include file="elib://admin/admin_footer.tpl"}


