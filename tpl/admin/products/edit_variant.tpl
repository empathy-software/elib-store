



{if isset($errors) and sizeof($errors) > 0}
    <ul id="error">
        {foreach from=$errors item=error}
            <li>{$error}</li>
        {/foreach}
    </ul>
{/if}

<h2>Edit Product Variant</h2>

<form method="post">
    <div class="form-group">
        <label for="weight_g">Weight (g)</label>
        <input type="text" class="form-control" id="weight_g" name="weight_g" value="{$variant->weight_g}" />
    </div>
    <div class="form-group">
        <label for="weight_lb">Weight (lb)</label>
        <input type="text" class="form-control" id="weight_lb" name="weight_lb" value="{$variant->weight_lb}" />
    </div>
    <div class="form-group">
        <label for="weight_oz">Weight (oz)</label>
        <input type="text" class="form-control" id="weight_oz" name="weight_oz" value="{$variant->weight_oz}" />
    </div>
    <div class="form-group">
        <label for="price">Price</label>
        <input type="text" class="form-control" id="price" name="price" value="{$variant->price}" />
    </div>

    <input type="hidden" name="id" value="{$variant->id}" />
    <button type="submit" class="btn btn-primary" name="save">Save</button>
    <button type="submit" class="btn btn-primary" name="cancel">Cancel</button>
</form>




