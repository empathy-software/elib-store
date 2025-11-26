


{include file="elib://comp_errors.tpl"}

<h2>Edit Product</h2>

<form method="post">
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name" value="{$product->name}" />
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea class="form-control" id="description" rows="12" name="description">{$product->description|escape}</textarea>
    </div>
    <div class="form-group">
        <label for="sold-in-store">Sold in store</label>
        <select class="form-control" id="sold-in-store" name="sold_in_store">
            {html_options options=$sold_in_store selected=$product->sold_in_store}
        </select>
    </div>
    <div class="form-group">
        <label for="brand">Brand</label>
        <select class="form-control" id="brand" name="brand_id">
            {html_options options=$brands selected=$product->brand_id}
        </select>
    </div>
    <input type="hidden" name="id" value="{$product->id}" />
    <button type="submit" class="btn btn-primary" name="submit_product">Save</button>
    <button type="submit" class="btn btn-primary" name="cancel">Cancel</button>
</form>

