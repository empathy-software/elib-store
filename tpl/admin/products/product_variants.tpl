


{if sizeof($variants) > 0}
    <div class="variants flex-wrap row">
        {section name=variant loop=$variants}
            <div class="card col-lg-4" >
                <img class="card-img-top" src="http://{$WEB_ROOT}{$PUBLIC_DIR}/{if $variants[variant].image eq ''}uploads/{$product->image}{else}uploads/{$variants[variant].image}{/if}" alt="" />
                <div class="card-body">
                    <div class="card-text">
                        <small class="text-muted">
                            <table class="table">
                                <tr>
                                    <th scope="row">Weight (g)</th>
                                    <td>{$variants[variant].weight_g}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Weight (lbs)</th>
                                    <td>{$variants[variant].weight_lb}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Weight (oz)</th>
                                    <td>{$variants[variant].weight_oz}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Price</th>
                                    <td>{$variants[variant].price}</td>
                                </tr>
                                {foreach from=$variants[variant].properties key=id item=property}
                                    <tr>
                                        <th scope="row">{$property.property_name}</th>
                                        <td>{$property.option_val}</td>
                                    </tr>
                                {/foreach}
                            </table>
                        </small>
                    </div>
                    <ul class="operations">
                        <li><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/edit_variant/{$variants[variant].id}">Edit</a></li>
                        <li><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/upload_variant_image/{$variants[variant].id}">Upload Image</a></li>
                        <li><a class="confirm" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/delete_variant/{$variants[variant].id}">Delete</a></li>
                        <li><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/variant_properties/{$variants[variant].id}">Properties</a></li>
                    </ul>
                </div>
            </div>
        {/section}
    </div>
{/if}
