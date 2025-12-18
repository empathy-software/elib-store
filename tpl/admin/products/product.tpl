
<div class="card js-image-card">
    {foreach item=image from=$product->getImages()}
    <img class="card-img-top img-fluid"
         src="http://{$WEB_ROOT}{$PUBLIC_DIR}/uploads/{$image.image}"
         alt="{$product->name}"
         style="object-fit: cover; height: 220px;"
    >
    {/foreach}

    <div class="card-body">
        <h5 class="card-title">
            {if $brand neq ''}{$brand} – {/if}{$product->name}
        </h5>

        <p class="card-text">
            <small class="text-muted">
                {$product->description}
            </small>
        </p>
    </div>
</div>