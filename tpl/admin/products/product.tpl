

<div class="card">
    <img class="card-img-top" src="http://{$WEB_ROOT}{$PUBLIC_DIR}/{if $product->image eq ''}img/blank.gif{else}uploads/{$product->image}{/if}" alt="" />
    <div class="card-body">
        <h5 class="card-title">{if $brand neq ''}{$brand} - {/if}{$product->name}</h5>
        <p class="card-text">
            <small class="text-muted">
                {$product->description}
            </small>
        </p>
    </div>
</div>

