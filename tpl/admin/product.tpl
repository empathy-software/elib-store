{include file="elib://admin/admin_header.tpl"}


{if in_array($event, array('default_event', 'edit', 'edit_colours', 'upload_image', 'upload_variant_image')) }
    <div class="form-group cms-actions">
        {if $event eq 'edit_colours'}
        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/add_colour/{$product->id}" class="btn btn-sm btn-primary">
            Add Colour
        </a>
        {else}
        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/edit/{$product->id}" class="btn btn-sm btn-primary">
            Edit
        </a>
        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/upload_image/{$product->id}" class="btn btn-sm btn-primary">
            Upload Image
        </a>
        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/delete/{$product->id}" class="confirm btn btn-sm btn-primary">
            Delete
        </a>
        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/add_variant/{$product->id}" class="btn btn-sm btn-primary">
            Add Variant
        </a>
        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/edit_colours/{$product->id}" class="btn btn-sm btn-primary">
            Edit Colours
        </a>
        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/variants_wizard/{$product->id}" class="{if (isset($variants) and sizeof($variants) > 0)}disabled{/if} btn btn-sm btn-primary">
            Variants Wizard
        </a>
        {/if}
    </div>
{/if}



<div class="row justify-content-between">
    <div class="col-md-3">
        {if in_array($event, array('default_event', 'edit', 'upload_image'))}
            {include file="elib://admin/products/product.tpl"}
        {elseif $event eq 'upload_variant_image'}
            {include file="elib://admin/products/variant.tpl"}
        {/if}
    </div>
    <div class="col-md-9">
        {if $event eq 'default_event'}
            {include file="elib://admin/products/product_variants.tpl"}
        {elseif $event eq 'edit'}
            {include file="elib://admin/products/edit_product.tpl"}
        {elseif $event eq 'upload_image'}
            {include file="elib://admin/products/upload_image.tpl"}
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
</div>



{include file="elib://admin/admin_footer.tpl"}
