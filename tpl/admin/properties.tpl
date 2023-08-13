{include file="elib://admin/admin_header.tpl"}


{if $event neq 'rename'}
    <div class="form-group cms-actions">
        <a class="btn btn-sm btn-primary" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/properties/add">Add</a>
    </div>
{/if}


{include file="elib://comp_errors.tpl"}


{if $event eq 'rename'}
    <h2>Rename Property</h2>
    <form method="post">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" value="{$property->name}" class="form-control" name="name" id="name">
        </div>
        <input type="hidden" name="id" value="{$artist->id}"/>
        <button type="submit" class="btn btn-primary" name="save">Save</button>
        <button type="submit" class="btn btn-primary" name="cancel">Cancel</button>
    </form>
{else}
    <div id="properties">
        {foreach from=$properties key=id item=property}
            <div class="property">
                <div class="form-row d-flex align-items-center">
                    <div class="col-sm-8 my-1">
                        <h2>{$property.name}</h2>
                    </div>
                    <div class="col-sm-4 my-1">
                        <a class="btn btn-sm btn-primary"
                           href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/properties/rename/{$id}">
                            Rename
                        </a>
                    </div>
                </div>
                <form action="" method="post">
                    {if isset($property.option) and sizeof($property.option) > 0}
                        {foreach from=$property.option item=option key=option_id}
                            <div class="form-group">
                                <label class="sr-only" for="option_{$option_id}">Name</label>
                                <span class="option" id="option_{$option_id}">{$option}</span>
                            </div>
                        {/foreach}
                    {/if}

                    <div class="form-row align-items-center">
                        <div class="col-sm-8 my-1">
                            <label class="sr-only" for="option">Name</label>
                            <input
                                    type="text"
                                    value="{if $submitted_option->property_id eq $id}{$submitted_option->option_val}{/if}"
                                    class="form-control"
                                    name="option"
                                    id="option"
                            />
                        </div>
                        <div class="col-sm-4 my-1">
                            <button class="btn btn-sm btn-primary" type="submit" name="add_option">Add</button>
                        </div>
                    </div>
                    <input type="hidden" name="id" value="{$id}"/>
                </form>
            </div>
        {/foreach}
    </div>
{/if}



{include file="elib://admin/admin_footer.tpl"}


