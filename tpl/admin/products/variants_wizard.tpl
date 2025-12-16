{include file="elib://comp_errors.tpl"}


<form action="" method="post">
    <fieldset class="mt-4">
        <legend>Base Variant Properties</legend>
        <div class="mb-3">
            <label class="form-label">Weight (g)</label>
            <input class="form-control" name="weight_g" type="text" value="{$variant->weight_g}"/>
        </div>
        <div class="mb-3">
            <label class="form-label">Weight (lb)</label>
            <input class="form-control" name="weight_lb" type="text" value="{$variant->weight_lb}"/>
        </div>
        <div class="mb-3">
            <label class="form-label">Weight (oz)</label>
            <input class="form-control" name="weight_oz" type="text" value="{$variant->weight_oz}"/>
        </div>
        <div class="mb-3">
            <label class="form-label">Price (&pound;)</label>
            <input class="form-control" name="price" type="text" value="{$variant->price}"/>
        </div>
    </fieldset>

    {if isset($colours) and sizeof($colours) > 0}
        <p>
            {foreach from=$colours item=colour}
                <input type="hidden" name="property[2][]" value="{$colour}"/>
            {/foreach}
        </p>
    {/if}

    {if sizeof($properties) > 0}
        <fieldset class="mt-4">
            <legend class="h5">Choose Variant Options</legend>

            {foreach from=$properties key=id item=property}
                <div class="mb-3">
                    <div class="form-label fw-semibold mb-2">{$property.name}</div>

                    {if sizeof($property.option) > 0}
                        {foreach from=$property.option item=option key=option_id}
                            <div class="form-check">
                                <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="prop-{$id}-opt-{$option_id}"
                                        name="property[{$id}][]"
                                        value="{$option_id}"
                                        checked
                                >
                                <label class="form-check-label" for="prop-{$id}-opt-{$option_id}">
                                    {$option}
                                </label>
                            </div>
                        {/foreach}
                    {/if}
                </div>
            {/foreach}

            <div class="mb-3">
                <input type="hidden" name="product_id" value="{$product->id}">
                <button class="btn btn-sm btn-primary" type="submit" name="submit">Submit</button>
            </div>
        </fieldset>

    {else}
        <p>This product is in a category with no active properties. Please select some before creating variants.</p>
    {/if}
</form>
