
{if $v.image neq ''}
    {assign var="image" value="uploads/`$v.image`"}
{elseif $product->image neq ''}
    {assign var="image" value="uploads/`$product->image`"}
{else}
    {assign var="image" value="img/blank.gif"}
{/if}

<div class="variant card {$class}">
    <img
            class="card-img-top img-fluid"
            src="http://{$WEB_ROOT}{$PUBLIC_DIR}/{$image}"
            alt=""
            style="object-fit: cover; height: 200px;"
    >

    <div class="card-body">

        {if isset($showActions) and $showActions}
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a class="btn btn-sm btn-primary"
                   href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/edit_variant/{$v.id}">
                    Edit
                </a>

                <a class="btn btn-sm btn-primary"
                   href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/upload_variant_image/{$v.id}">
                    Upload Image
                </a>

                <a class="confirm btn btn-sm btn-danger"
                   href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/delete_variant/{$v.id}">
                    Delete
                </a>

                <a class="btn btn-sm btn-primary"
                   href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/variant_properties/{$v.id}">
                    Properties
                </a>
            </div>
        {/if}

        <div class="card-text small">
            <table class="table table-sm mb-0">
                <tbody>
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

                {foreach from=$v.properties item=property}
                    <tr>
                        <th scope="row" class="text-primary">
                            {$property.property_name}
                        </th>
                        <td>{$property.option_val}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>

    </div>
</div>