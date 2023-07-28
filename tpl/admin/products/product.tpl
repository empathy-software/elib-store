

<div class="card">
    <img class="card-img-top" src="http://{$WEB_ROOT}{$PUBLIC_DIR}/{if $product->image eq ''}elib/blank.gif{else}uploads/{$product->image}{/if}" alt="" />
    <div class="card-body">
        <h5 class="card-title">{$brand} - {$product->name}</h5>
        <p class="card-text">
            <small class="text-muted">
                {$product->description}
            </small>
        </p>
    </div>
</div>

