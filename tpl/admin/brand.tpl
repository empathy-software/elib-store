{include file="elib://admin/admin_header.tpl"}



<div class="form-group cms-actions">
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/brand/add" class="btn btn-sm btn-primary">
        Add
    </a>
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/brand/edit_bio/{$artist->id}" class="btn btn-sm btn-primary {if $event eq 'edit_bio'}disabled={/if}">
        Edit Bio
    </a>
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/brand/rename/{$artist->id}" class="btn btn-sm btn-primary {if $event eq 'rename'}disabled{/if}">
        Rename
    </a>
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/brand/delete/{$artist->id}" class="confirm btn btn-sm btn-primary {if $event eq 'rename'}disabled{/if}">
        Delete
    </a>
</div>



<div class="row">
    <div class="col-md-5">
        {$banners}
    </div>

    <div class="col-md-7">

        {include file="elib://comp_errors.tpl"}

        {if $event eq 'rename'}
            {include file="elib://admin/rename_brand.tpl"}
        {elseif $event eq 'edit_bio'}
            {include file="elib://admin/edit_brand_bio.tpl"}
        {/if}
    </div>
</div>






{include file="elib://admin/admin_footer.tpl"}