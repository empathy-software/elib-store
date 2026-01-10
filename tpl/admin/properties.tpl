{include file="elib://admin/admin_header.tpl"}


{if $event neq 'rename'}
    <div class="form-group mt-4 mb-4 cms-actions">
        <a class="btn btn-sm btn-primary" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/properties/add">Add</a>
    </div>
{/if}


{include file="elib://comp_errors.tpl"}



{if $event eq 'rename'}
    <h2>Rename Property</h2>

    <form method="post">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input
                    type="text"
                    value="{$property->name|escape}"
                    class="form-control"
                    name="name"
                    id="name"
            >
        </div>
        <div class="mb-3">
            <input type="hidden" name="id" value="{$artist->id|escape}">
            <button type="submit" class="btn btn-sm btn-primary" name="save" value="1">Save</button>
            <a class="btn btn-sm btn-outline-secondary" href="{$PUBLIC_DIR}/admin/properties">Cancel</a>
        </div>
    </form>

{else}
    <div id="properties">
        {foreach from=$properties key=id item=property}
            <div class="property mb-4">

                <div class="row g-2 align-items-center mb-2 mt-5">
                    <div class="col-sm-8">
                        <h2 class="h5 mb-0">{$property.name|escape}</h2>
                    </div>
                    <div class="col-sm-4 text-sm-end">
                        <a class="btn btn-sm btn-primary"
                           href="{$PUBLIC_DIR}/admin/properties/rename/{$id|escape}">
                            Rename
                        </a>
                    </div>
                </div>

                <form method="post">
                    {if !empty($property.option)}
                        {foreach from=$property.option item=option key=option_id}
                            <div class="mb-2">
                                <span class="option" id="option_{$option_id}">{$option|escape}</span>
                            </div>
                        {/foreach}
                    {/if}

                    <div class="row g-2 align-items-center mb-5">
                        <div class="col-sm-10">
                            <label class="visually-hidden" for="new_option_{$id}">Name</label>
                            <input
                                    type="text"
                                    class="form-control"
                                    name="option"
                                    id="new_option_{$id}"
                                    value="{if isset($submitted_option) && $submitted_option->property_id eq $id}{$submitted_option->option_val|escape}{/if}"
                            >
                        </div>
                        <div class="col-sm-2">
                            <button class="btn btn-sm btn-primary w-100" type="submit" name="add_option" value="1">
                                Add
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="id" value="{$id|escape}">
                </form>
            </div>
        {/foreach}
    </div>
{/if}



{include file="elib://admin/admin_footer.tpl"}


