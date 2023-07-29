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

    {if sizeof($p_nav) > 1}
    {assign var="lastPage" value=0}
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-end">
                <li class="page-item{if $page eq 1} disabled{/if}">
                    <a class="page-link" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/category/{$category->id}/?page={$page - 1}" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                        <span class="sr-only">Previous</span>
                    </a>
                </li>
                {foreach from=$p_nav key=k item=v}
                <li class="page-item{if $v} disabled{/if}">
                    <a class="page-link" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/category/{$category->id}/?page={$k}">
                        {$k}
                    </a>
                </li>
                {assign var="lastPage" value=$k}
                {/foreach}
                <li class="page-item{if $page eq $lastPage} disabled{/if}">
                    <a class="page-link" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/category/{$category->id}/?page={$page + 1}" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                        <span class="sr-only">Next</span>
                    </a>
                </li>
            </ul>
        </nav>
    {/if}
{/if}