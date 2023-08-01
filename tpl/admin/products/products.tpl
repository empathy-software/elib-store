{if $category_has_children == 0}
    {if sizeof($products) < 1}
        <p>No products to display.</p>
    {else}
        <table class="table">
            <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Name</th>
                <th scope="col">Description</th>
                <th scope="col">Sold In Store</th>
            </tr>
            </thead>
            <tbody>
            {section name=product_item loop=$products}
                <tr class="{cycle values="alt,"}">
                    <th scope="row" class="id">{$products[product_item].id}</th>
                    <td>
                        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/{$products[product_item].id}/">{$products[product_item].name}</a>
                    </td>
                    <td>{$products[product_item].description|strip_tags|truncate:50:"..."}</td>
                    <td>{if $products[product_item].sold_in_store eq 1}Yes{else}&nbsp;{/if}</td>
                </tr>
            {/section}
            </tbody>
        </table>
    {/if}

    {include file="elib://comp_pagination.tpl"}
{/if}