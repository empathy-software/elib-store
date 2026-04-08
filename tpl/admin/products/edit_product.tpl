


{include file="elib://comp_errors.tpl"}

<h2>Edit Product</h2>

<form method="post">

    <div class="mb-3">
        <label  class="form-label" for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name" value="{$product->name}" />
    </div>
    <div class="mb-3">
        <label class="form-label"for="description">Description</label>
        <textarea class="form-control" id="description" rows="12" name="description">{$product->description|escape}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label" for="sold-in-store">Sold in store</label>
        <select class="form-control" id="sold-in-store" name="sold_in_store">
            {html_options options=$sold_in_store selected=$product->getSoldInStore()}
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="brand">Brand</label>
        <select class="form-control" id="brand" name="brand_id">
            {html_options options=$brands selected=$product->brand_id}
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="shipping_uk">Shipping (UK)</label>
        <input type="text" class="form-control" id="shipping_uk" name="shipping_uk" value="{$product->shipping_uk}" />
    </div>
    <div class="mb-3">
        <label class="form-label" for="shipping_eu">Shipping (EU)</label>
        <input type="text" class="form-control" id="shipping_eu" name="shipping_eu" value="{$product->shipping_eu}" />
    </div>
    <div class="mb-3">
        <label class="form-label" for="shipping_other">Shipping (Other)</label>
        <input type="text" class="form-control" id="shipping_other" name="shipping_other" value="{$product->shipping_other}" />
    </div>
    <div class="mb-3">
        <input type="hidden" name="id" value="{$product->id}" />
        <button type="submit" class="btn btn-primary" name="submit_product">Save</button>
        <button type="submit" class="btn btn-primary" name="cancel">Cancel</button>
    </div>

</form>

