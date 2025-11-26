

{if $v.image neq ''}
    {assign var="image" value="uploads/`$v.image`"}
{elseif $product->image neq ''}
    {assign var="image" value="uploads/`$product->image`"}
{else}
    {assign var="image" value="img/blank.gif"}
{/if}

<div class="variant card {$class}" >
    <img class="card-img-top" src="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$image}" alt="" />
    <div class="card-body">

        {if isset($showActions) and $showActions}
            <ul class="actions">
                <li><a class="btn btn-sm btn-primary" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/edit_variant/{$v.id}">Edit</a></li>
                <li><a class="btn btn-sm btn-primary" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/upload_variant_image/{$v.id}">Upload Image</a></li>
                <li><a class="confirm btn btn-sm btn-primary" class="confirm" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/delete_variant/{$v.id}">Delete</a></li>
                <li><a class="btn btn-sm btn-primary" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/variant_properties/{$v.id}">Properties</a></li>
            </ul>
        {/if}

        <div class="card-text">
            <small class="text-muted">
                <table class="table">
                    <tr>
                        <th scope="row">Weight (g)</th>
                        <td>{$v.weight_g}</td>
                    </tr>
                    <tr>
                        <th scope="row">Weight (lbs)</th>
                        <td>{$v.weight_lb}</td>
                    </tr>
                    <tr>
                        <th scope="row">Weight (oz)</th>
                        <td>{$v.weight_oz}</td>
                    </tr>
                    <tr>
                        <th scope="row">Price</th>
                        <td>{$v.price}</td>
                    </tr>
                    {foreach from=$v.properties key=id item=property}
                        <tr>
                            <th scope="row"><span class="text-primary">{$property.property_name}</span></th>
                            <td>{$property.option_val}</td>
                        </tr>
                    {/foreach}
                </table>
            </small>
        </div>


    </div>
</div>
