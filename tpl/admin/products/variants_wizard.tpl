


{if isset($errors) and sizeof($errors) > 0}
<ul id="error">
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}


<form action="" method="post">
<fieldset>
<legend>Base Variant Properties</legend>
<p><label>Weight (g)</label>
<input name="weight_g" type="text" value="{$variant->weight_g}" /></p>
<p><label>Weight (lb)</label>
<input name="weight_lb" type="text" value="{$variant->weight_lb}" /></p>
<p><label>Weight (oz)</label>
<input name="weight_oz" type="text" value="{$variant->weight_oz}" /></p>
<p><label>Price (&pound;)</label>
<input name="price" type="text" value="{$variant->price}" /></p>
</fieldset>

<p>&nbsp;</p>
<p>&nbsp;</p>

{if isset($colours) and sizeof($colours) > 0}
<p>
{foreach from=$colours item=colour}
<input type="hidden" name="property[2][]" value="{$colour}" />
{/foreach}
</p>
{/if}




{if sizeof($properties) > 0}
<fieldset><legend>Choose Variant Options</legend>


{foreach from=$properties key=id item=property}
<p><label>{$property.name}</label>
<span class="checkboxes">
{if sizeof($property.option) > 0}
{foreach from=$property.option item=option key=option_id}
<label>
<input type="checkbox" name="property[{$id}][]" value="{$option_id}" checked="checked" />
{*<input type="checkbox" name="property[{$id}]" value="{$option_id}"{if in_array($option_id, $options)} selected="selected"{/if} />*}
{$option}</label><br />
{/foreach}
{/if}
</span>
</p>
{/foreach}

<p><label>&nbsp;</label>
<input type="hidden" name="product_id" value="{$product->id}" />
<button type="submit" name="submit">Submit</button>
</p>
</fieldset>
</form>
{else}
<p>This product is in a category with no active properties. Please select some before creating variants.</p>
{/if}

