

<h2>Active Properties</h2>
<p>&nbsp;</p>

<form method="post">
    {foreach from=$properties key=id item=property}

        {if !preg_match('/New Property$/', $property.name)}
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                       name="property[{$id}]" {if in_array($id, $active_properties) || in_array($id, $inherited_properties)} checked="checked"{/if}{if in_array($id, $inherited_properties)} disabled="disabled"{/if} />
                <label class="form-check-label" for="property[{$id}]">
                    {$property.name}
                </label>
            </div>
        {/if}
    {/foreach}
    <p>&nbsp;</p>
    <button type="submit" class="btn btn-primary" name="save">Save</button>
    <button type="submit" class="btn btn-primary" name="cancel">Cancel</button>
</form>
