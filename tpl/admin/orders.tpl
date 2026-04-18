{include file="elib://admin/admin_header.tpl"}


<div class="accordion" id="ordersAccordion">
    {foreach from=$orders item=order name=ordersLoop}

        <div class="accordion-item mb-3 border rounded-3 overflow-hidden">
            <h2 class="accordion-header" id="heading{$order.id}">
                <button
                        class="accordion-button {if not $smarty.foreach.ordersLoop.first}collapsed{/if}"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse{$order.id}"
                        aria-expanded="{if $smarty.foreach.ordersLoop.first}true{else}false{/if}"
                        aria-controls="collapse{$order.id}"
                >
                    <div class="container-fluid px-0">
                        <div class="row w-100 align-items-center g-2 text-start">
                            <div class="col-12 col-md-3">
                                <div class="fw-semibold">Order #{$order.id}</div>
                                {if isset($order.invoice) && $order.invoice}
                                    <div class="small text-muted">Invoice: {$order.invoice}</div>
                                {/if}
                            </div>

                            <div class="col-6 col-md-2">
                                <div class="small text-muted">Customer</div>
                                <div>{$order.username|escape}</div>
                            </div>

                            <div class="col-6 col-md-2">
                                <div class="small text-muted">Status</div>
                                <div>
                                    <span class="badge
                                        {if $order.status eq 'Completed'}bg-success
                                        {elseif $order.status eq 'Pending'}bg-warning text-dark
                                        {elseif $order.status eq 'Cancelled'}bg-danger
                                        {else}bg-secondary{/if}">
                                        {$order.status|escape}
                                    </span>
                                </div>
                            </div>

                            <div class="col-6 col-md-2">
                                <div class="small text-muted">Value</div>
                                <div>&pound;{$order.total|string_format:"%.2f"}</div>
                            </div>

                            <div class="col-6 col-md-3">
                                <div class="small text-muted">Date</div>
                                <div>{$order.stamp|date_format:"%d/%m/%Y @ %H:%M:%S"}</div>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>

            <div
                    id="collapse{$order.id}"
                    class="accordion-collapse collapse {if $smarty.foreach.ordersLoop.first}show{/if}"
                    aria-labelledby="heading{$order.id}"
                    data-bs-parent="#ordersAccordion"
            >
                <div class="accordion-body bg-light">

                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Order number</div>
                            <div class="fw-semibold">{$order.id}</div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Items</div>
                            <div class="fw-semibold">{count($order.items)}</div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Customer</div>
                            <div class="fw-semibold">{$order.username|escape}</div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="small text-muted">Order total</div>
                            <div class="fw-semibold">&pound;{$order.total|string_format:"%.2f"}</div>
                        </div>
                    </div>

                    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 row-cols-xxl-4 g-3">
                        {foreach from=$order.items item=p}
                            {assign var=image value=$p.product->getDefaultImage()}

                            <div class="col">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="position-relative">
                                        {if $p.brand neq 'General' and $p.product->getStock() < 1}
                                            <span class="badge bg-dark position-absolute top-0 start-0 m-2">Sold out</span>
                                        {/if}

                                        {if $p.path}
                                        <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/store{$p.path}">
                                            {/if}

                                            <img
                                                    class="card-img-top img-fluid"
                                                    src="http://{$WEB_ROOT}{$PUBLIC_DIR}/uploads/{$image.image}"
                                                    alt="{$p.name|escape}"
                                                    style="aspect-ratio: 4 / 5; object-fit: cover;"
                                            >

                                            {if $p.path}
                                        </a>
                                        {/if}
                                    </div>

                                    <div class="card-body d-flex flex-column">
                                        <h6 class="card-title mb-2">
                                            {$p.brand|escape} - {$p.name|escape}
                                        </h6>

                                        <div class="small text-muted mb-3">
                                            Item no: {$p.id}
                                        </div>

                                        <dl class="row small mb-0">
                                            <dt class="col-5 text-muted">Price</dt>
                                            <dd class="col-7 mb-2">&pound;{$p.price|string_format:"%.2f"}</dd>

                                            <dt class="col-5 text-muted">Quantity</dt>
                                            <dd class="col-7 mb-2">{$p.quantity}</dd>

                                            {if isset($p.notes) && $p.notes}
                                                <dt class="col-5 text-muted">Notes</dt>
                                                <dd class="col-7 mb-2">{$p.notes|escape}</dd>
                                            {/if}

                                            {if isset($p.on0) && isset($p.os0)}
                                                <dt class="col-5 text-muted">Option</dt>
                                                <dd class="col-7 mb-2">{$p.on0|escape}: {$p.os0|escape}</dd>
                                            {/if}
                                        </dl>

                                        {if $p.path}
                                            <div class="mt-3">
                                                <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/store{$p.path}" class="btn btn-sm btn-outline-dark">
                                                    View product
                                                </a>
                                            </div>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                    </div>

                </div>
            </div>
        </div>

    {/foreach}
</div>


{include file="elib://admin/admin_footer.tpl"}
