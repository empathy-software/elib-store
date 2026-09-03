{include file="elib://admin/admin_header.tpl"}


<div class="mt-4 mb-4 cms-actions">
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/properties" class="btn btn-sm btn-primary">
        Manage Properties
    </a>
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/category/add_category/{$category->id}" class="btn btn-sm btn-primary {if isset($products) and sizeof($products) > 0}disabled{/if}">
        Add Category
    </a>
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/category/rename/{$category->id}" class="btn btn-sm btn-primary {if $category->id eq 0 || $event eq 'rename'} disabled{/if}">
        Rename Category
    </a>
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/category/edit_description/{$category->id}" class="btn btn-sm btn-primary {if $category->id eq 0 || $event eq 'edit_description'} disabled{/if}">
        Edit Description
    </a>
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/category/delete/{$category->id}" class="confirm btn btn-sm btn-primary {if $category->id eq 0} disabled{/if}">
        Delete Category
    </a>
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/add/{$category->id}" class="btn btn-sm btn-primary {if $category_has_children} disabled{/if}">
        Add Product
    </a>
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/category/active_properties/{$category->id}" class="btn btn-sm btn-primary {if $category->id eq 0 || $event eq 'active_properties'} disabled{/if}">
        Active Properties
    </a>
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/resize_images" class="btn btn-sm btn-primary">
        Resize Image
    </a>
</div>


{if $category_id != 0}
<p><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/category/0/?page=1">Top Level</a></p>
{/if}

<div class="row">
    <div class="col-md-5">
        {$nav}
    </div>
    <div class="col-md-7">
        {if $event eq 'rename'}
        {include file="elib://admin/products/rename.tpl"}
        {elseif $event eq 'edit_description'}
        {include file="elib://admin/products/edit_description.tpl"}
        {elseif $event eq 'active_properties'}
        {include file="elib://admin/products/active_properties.tpl"}
        {elseif $class eq 'product'}
        {include file="elib://admin/products/edit_product.tpl"}
        {elseif $category_has_children == 0}
        {include file="elib://admin/products/products.tpl"}
        {/if}
    </div>
</div>



{include file="elib://admin/admin_footer.tpl"}
