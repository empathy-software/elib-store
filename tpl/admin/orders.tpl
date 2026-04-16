{include file="elib://admin/admin_header.tpl"}


<table class="table">
    <thead>
    <tr>
        <th>Order No / Invoice No</th>
        <th>Customer</th>
        <th>Status</th>
        <th>Value</th>
        <th>Date</th>
    </tr>
    </thead>
    <tbody>
    {foreach from=$orders item=order}
        <tr>
            <td><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/order/{$order.id}">00{$order.id}
                    / {$order.stamp|date_format:"%d%m%y"}</a></td>
            <td>{$order.username}</td>
            <td>{$order.status}</td>
            <td>{$order.total}</td>
            <td>{$order.stamp|date_format:"%d/%m/%y @ %H:%M:%S"}</td>
        </tr>
        <tr>
            <td colspan="5">

                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">

                    {foreach from=$order.items item=p}

                        {assign var=image value=$p.product->getDefaultImage()}
                        <div class="col">
                            <div class="card h-100">

                                <p>Item price: {$p.price}</p>
                                <p>Quantity: {$p.quantity}</p>

                                <div class="card-image">
                                    <img class="card-img-top img-fluid mb-3" src="http://{$WEB_ROOT}{$PUBLIC_DIR}/uploads/{$image.image}"
                                         alt=""/>
                                </div>
                                <div class="card-body">
                                    <div class="card">
                                        <div class="card-body">
                                            {if $p.brand neq 'General' and $p.product->getStock() < 1}
                                                <p>SOLD OUT</p>
                                            {/if}
                                            <h5 class="card-title">{$p.brand} - {$p.name|replace:"&":"&amp;"}</h5>
                                        </div>
                                    </div>
                                </div>
                                {if $p.path}
                                <a
                                    href="http://{$WEB_ROOT}{$PUBLIC_DIR}/store{$p.path}"
                                    class="stretched-link"
                                ></a>
                                {/if}
                            </div>
                        </div>
                    {/foreach}
                </div>
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>


{include file="elib://admin/admin_footer.tpl"}
