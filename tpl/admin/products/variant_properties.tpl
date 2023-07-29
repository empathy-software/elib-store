




<h2>Variant Properties</h2>

{if sizeof($properties) > 0}
<form method="post">
    {foreach from=$properties key=id item=property}
        <div class="form-group">
            <label for="property-{$id}">{$property.name}</label>
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
    <input type="hidden" name="id" value="{$product->id}" />
    <button type="submit" class="btn btn-primary" name="save">Save</button>
    <button type="submit" class="btn btn-primary" name="cancel">Cancel</button>
</form>
{else}
    <p>Product is in a category with no active properties.</p>
{/if}



