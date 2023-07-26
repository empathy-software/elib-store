{include file="elib://admin/admin_header.tpl"}



{if $event eq 'default_event' || $event eq 'edit' || $event eq 'edit_colours'}
<div id="operations">

<div class="grey" style="padding:0.5em;">

{if $event eq 'edit_colours'}

<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/add_colour/{$product->id}" method="get">
<div><button type="submit" name="add_colour" value="1">Add Colour</button></div>
</form>


{else}

<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/edit/{$product->id}" method="get">
<div><button type="submit" name="edit" value="1">Edit</button></div>
</form>
<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/upload_image/{$product->id}" method="get">
<div><button type="submit" name="upload_image" value="1">Upload Image</button></div>
</form>
<form class="confirm" action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/delete/{$product->id}" method="get">
<div><button type="submit" name="delete_product" value="1">Delete</button></div>
</form>
<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/add_variant/{$product->id}" method="get">
<div><button type="submit" name="add_variant" value="1">Add Variant</button></div>
</form>
<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/edit_colours/{$product->id}" method="get">
<div><button type="submit" name="edit_colours" value="1">Edit Colours</button></div>
</form>
    <form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/variants_wizard/{$product->id}" method="get">
        <div><button type="submit" name="variants_wizard" value="1"{if (isset($variants) and sizeof($variants) > 0)} disabled="disabled"{/if}>Variants Wizard</button></div>
    </form>
{*
<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/variants_wizard/{$product->id}" method="get">
<div><button type="submit" name="variants_wizard" value="1"{if (isset($variants) and sizeof($variants) > 0) || !$has_colours} disabled="disabled"{/if}>Variants Wizard</button></div>
</form>
*}

{/if}

</div>
</div>


<p style="line-height: 0.5em;">&nbsp;</p>
{/if}


<div class="grey clear">


<div id="categories">
{$nav}
</div>


{if $event eq 'edit'}
{include file="elib://admin/products/edit_product.tpl"}
{elseif $event eq 'upload_image'}
{include file="elib://admin/products/upload_image.tpl"}
{elseif $event eq 'default_event'}
{include file="elib://admin/products/product.tpl"}
{elseif $event eq 'edit_variant'}
{include file="elib://admin/products/edit_variant.tpl"}
{elseif $event eq 'upload_variant_image'}
{include file="elib://admin/products/upload_variant_image.tpl"}
{elseif $event eq 'variant_properties'}
{include file="elib://admin/products/variant_properties.tpl"}
{elseif $event eq 'resize_images'}
{include file="elib:/admin/products/resize_images.tpl"}
{elseif $event eq 'edit_colours'}
{include file="elib://admin/products/edit_colours.tpl"}
{elseif $event eq 'add_colour'}
{include file="elib://admin/products/add_colour.tpl"}
{elseif $event eq 'edit_colour'}
{include file="elib://admin/products/edit_colour.tpl"}
{elseif $event eq 'variants_wizard'}
{include file="elib://admin/products/variants_wizard.tpl"}
{/if}






</div>






{include file="elib://admin/admin_footer.tpl"}
