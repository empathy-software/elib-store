




<h2>Variant Properties</h2>

{if isset($properties) and sizeof($properties) > 0}
<form method="post">
    {foreach from=$properties key=id item=property}
        <div class="mb-3">
            <label for="form-label property-{$id}">{$property.name}</label>
            <select class="form-control" id="property-{$id}" name="property[{$id}]">
                <option value="0">Null</option>
                {if sizeof($property.option) > 0}
                    {foreach from=$property.option item=option key=option_id}
                        <option value="{$option_id}"{if in_array($option_id, $options)} selected="selected"{/if}>{$option}</option>
                    {/foreach}
                {/if}
            </select>
        </div>
    {/foreach}
    <div class="mb-3">
        <input type="hidden" name="id" value="{$product->id}" />
        <button type="submit" class="btn btn-sm btn-primary" name="save">Save</button>
        <button type="submit" class="btn btn-sm btn-primary" name="cancel">Cancel</button>
    </div>
</form>
{else}
    <p>Product is in a category with no active properties.</p>
{/if}



