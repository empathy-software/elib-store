




<div class="product">
<img src="http://{$WEB_ROOT}{$PUBLIC_DIR}/{if $product->image eq ''}elib/blank.gif{else}uploads/tn_{$product->image}{/if}" alt="" />
</div>

{if isset($errors) and sizeof($errors) > 0}
<ul id="error">
{foreach from=$errors item=error}
<li>{$error}</li>
{/foreach}
</ul>
{/if}

<fieldset><legend>Edit Product</legend>
<form action="" method="post">
<p><label>Name</label>
<input name="name" type="text" value="{$product->name}" /></p>
<!--<p><label>Category</label>
<select name="category">
{html_options options=$categories selected=$product->category_id}
</select></p>-->
<p><label>Description</label>
<textarea cols="" rows="" name="description">{$product->description|replace:'</p><p>':"\r\n"|replace:'<p>':""|replace:'</p>':""}</textarea></p>

{*
<p><label>Price</label>
<input type="text" name="price" value="{$product->price}" /></p>*}

{*
<p>Ranges</p>

{foreach from=$ranges item=range key=key}
<p><label for="range_{$key}">{$range}</label><input id="range_{$key}" type="checkbox" name="range[]" value={$key} {if in_array($key, $product_ranges)}checked{/if} /></p>
{/foreach}*}


<p>
<label>Sold In Store</label>
<select name="sold_in_store">
{html_options options=$sold_in_store selected=$product->sold_in_store}
</select>
</p>


<p>
<label>Brand</label>
<select name="brand_id">
{html_options options=$brands selected=$product->brand_id}
</select>
</p>


<p><label>&nbsp;</label>
<input type="hidden" name="id" value="{$product->id}" />
<button type="submit" name="submit_product">Save</button></p>
</form>
</fieldset>
<div class="clear">&nbsp;</div>

