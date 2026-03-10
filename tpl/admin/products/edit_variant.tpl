



{include file="elib://comp_errors.tpl"}

<h2>Edit Product Variant</h2>

<form method="post">
    <div class="mb-3">
        <label class="form-label" for="weight_g">Weight (g)</label>
        <input type="text" class="form-control" id="weight_g" name="weight_g" value="{$variant->weight_g}" />
    </div>
    <div class="mb-3">
        <label class="form-label" for="weight_lb">Weight (lb)</label>
        <input type="text" class="form-control" id="weight_lb" name="weight_lb" value="{$variant->weight_lb}" />
    </div>
    <div class="mb-3">
        <label class="form-label" for="weight_oz">Weight (oz)</label>
        <input type="text" class="form-control" id="weight_oz" name="weight_oz" value="{$variant->weight_oz}" />
    </div>
    <div class="mb-3">
        <label class="form-label" for="price">Price</label>
        <input type="text" class="form-control" id="price" name="price" value="{$variant->price}" />
    </div>
    <div class="mb-3">
        <label class="form-label" for="stock">Stock</label>
        <input type="number" class="form-control" id="stock" name="stock" value="{$variant->stock}" />
    </div>
    <div class="mb-3">
        <input type="hidden" name="id" value="{$variant->id}" />
        <button type="submit" class="btn btn-sm btn-primary" name="save">Save</button>
        <button type="submit" class="btn btn-sm btn-primary" name="cancel">Cancel</button>
    </div>
</form>




