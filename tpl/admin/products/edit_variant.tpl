




<div class="product">
<img src="http://{$WEB_ROOT}{$PUBLIC_DIR}/{if $variant->image eq ''}elib/blank.gif{else}uploads/mid_{$variant->image}{/if}" alt="" />
</div>


{if isset($errors) and sizeof($errors) > 0}
<ul id="error">
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}



<fieldset><legend>Edit Product Variant</legend>
<form action="" method="post">
<p><label>Weight (g)</label>
<input name="weight_g" type="text" value="{$variant->weight_g}" /></p>
<p><label>Weight (lb)</label>
<input name="weight_lb" type="text" value="{$variant->weight_lb}" /></p>
<p><label>Weight (oz)</label>
<input name="weight_oz" type="text" value="{$variant->weight_oz}" /></p>
<p><label>Price (&pound;)</label>
<input name="price" type="text" value="{$variant->price}" /></p>


{*
<p><label>Price</label>
<input type="text" name="price" value="{$product->price}" /></p>*}

{*
<p>Ranges</p>

{foreach from=$ranges item=range key=key}
<p><label for="range_{$key}">{$range}</label><input id="range_{$key}" type="checkbox" name="range[]" value={$key} {if in_array($key, $product_ranges)}checked{/if} /></p>
{/foreach}*}

<p><label>&nbsp;</label>
<input type="hidden" name="id" value="{$variant->id}" />
<button type="submit" name="save">Save</button></p>
</form>
</fieldset>
<div class="clear">&nbsp;</div>

