



<div id="right">

<div id="products">

{if $category_has_children == 0}
{if sizeof($products) < 1}
<p>No products to display.</p>
{else}
<table>
<tr>
{*
<th><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/category/{$category->id}/?order_by=id{if $order_by eq 'id'}%20DESC{/if}">ID</a></th>
<th><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/category/{$category->id}/?order_by=name{if $order_by eq 'name'}%20DESC{/if}">Name</a></th>
<th><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/category/{$category->id}/?order_by=description{if $order_by eq 'description'}%20DESC{/if}">Description</a></th>
<th><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/category/{$category->id}/?order_by=image{if $order_by eq 'image'}%20DESC{/if}">Image</a></th>
*}
<th>ID</th>
<th>Name</th>
<th>Description</th>
<th>Sold In Store</th>
{*<th>Image</th>*}
{*
<th><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/category/{$category->id}/?order_by=price{if $order_by eq 'price'}%20DESC{/if}">Price</a></th>
<th>Stock</th>
*}

<th>&nbsp;</th>
</tr>
{section name=product_item loop=$products}
<tr class="{cycle values="alt,"}">
<td class="id">{$products[product_item].id}</td>
<td><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/{$products[product_item].id}/">{$products[product_item].name}</a></td>
<td>{$products[product_item].description|strip_tags|truncate:50:"..."}</td>
<td>{if $products[product_item].sold_in_store eq 1}Yes{else}&nbsp;{/if}</td>
{*<td>
<img src="http://{$WEB_ROOT}{$PUBLIC_DIR}/img/{if $products[product_item].image eq ''}blank.gif{else}uploads/{$products[product_item].image}{/if}" alt="" width="66" /></td>*}
<!--<td>&pound;{$products[product_item].price}</td>-->
<!--<td>{$products[product_item].stock}</td>-->
<td>&nbsp;
<!--
<a class="action" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/edit/{$products[product_item].id}/">Edit</a><br />
<a class="action" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/attributes/{$products[product_item].id}/">Attributes</a><br />
<a class="action" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/stock/{$products[product_item].id}/">Stock</a><br />
<a class="action" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/upload_image/{$products[product_item].id}/">Upload Image</a><br />
<a class="action" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/remove/{$products[product_item].id}/?category={$category->id}/">Remove</a>
-->

</td>
</tr>
{/section}
</table>

{/if}

<p>&nbsp;</p>

{if sizeof($p_nav) > 1}
<div id="p_nav">
<p>
{foreach from=$p_nav key=k item=v}
{if $v eq 1}<span>{$k}</span>
{else}
<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/category/{$category->id}/?page={$k}">{$k}</a>
{/if}
{/foreach}
</p>
</div>
{else}
<p>&nbsp;</p>
{/if}

{/if}

</div>
</div>
