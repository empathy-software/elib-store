




<div class="product">
<img src="http://{$WEB_ROOT}{$PUBLIC_DIR}/{if $promo->image eq ''}img/blank.gif{else}uploads/mid_{$promo->image}{/if}" alt="" />
<h3>{$promo->name}</h3>

</div>

{if isset($variants) and sizeof($variants) > 0}
<div id="variants">



{section name=variant loop=$variants}
<div class="variant">

<table>
{foreach from=$variants[variant].properties key=id item=property}
<tr>
<th>{$property.property_name}</th>
<td>{$property.option_val}</td>
</tr>
{/foreach}
<tr>
<th>Weight (g)</th>
<td>{$variants[variant].weight_g}</td>
</tr>
<tr>
<th>Weight (lb)</th>
<td>{$variants[variant].weight_lb}</td>
</tr>
<tr>
<th>Price</th>
<td>{$variants[variant].price}</td>
</tr>
</table>

<div class="img">
<img src="http://{$WEB_ROOT}{$PUBLIC_DIR}/{if $variants[variant].image eq ''}img/blank.gif{else}uploads/tn_{$variants[variant].image}{/if}" alt="" />
</div>

<ul class="operations">
<li><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/edit_variant/{$variants[variant].id}">Edit</a></li>
<li><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/upload_variant_image/{$variants[variant].id}">Upload Image</a></li>
<li><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/delete_variant/{$variants[variant].id}">Delete</a></li>
<li><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/variant_properties/{$variants[variant].id}">Properties</a></li>
</ul>



<p class="clear">&nbsp;</p>
</div>
{/section}
</div>
{/if}
