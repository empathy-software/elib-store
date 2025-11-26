{include file="elib://admin/admin_header.tpl"}

{if $artist->id > 0 && $artist->active neq 1}
<div id="notice">
<p>This artist is currently hidden so will not be visible on the site. This applies to their biography and also their prints. To change this click 'Show Artist'.</p>
</div>
{/if}

<div class="form-group cms-actions">

    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/artist/add" class="btn btn-sm btn-primary">
        Add New Artist
    </a>
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/artist/edit_bio/{$artist->id}" class="btn btn-sm btn-primary {if $event eq 'edit_bio' || $artist->id eq 0}disabled{/if}">
        Add/Edit Bio
    </a>
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/artist/rename/{$artist->id}" class="btn btn-sm btn-primary {if $event eq 'rename' || $artist->id eq 0}disabled{/if}">
        Rename Artist
    </a>
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/artist/upload_image/{$artist->id}" class="btn btn-sm btn-primary {if $artist->id eq 0} disabled{/if}">
        Upload Artist Photo
    </a>
    <a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/artist/toggle_active/{$artist->id}" class="btn btn-sm btn-primary {if $artist->id eq 0}disabled{/if}">
        {if $artist->active}Hide Artist{else}Show Artist{/if}
    </a>
</div>
{*
<form action="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/artist/delete/{$artist->id}" method="get">
<div><button type="submit" name="delete" value="1">Delete</button></div>
</form>
*}


<div class="row">
    <div class="col-md-5">
        {$banners}
    </div>

    <div class="col-md-7">
        {if $event eq 'rename'}
        {include file="elib://admin/rename_artist.tpl"}
        {elseif $event eq 'edit_bio'}
        {include file="elib://admin/edit_artist_bio.tpl"}
        {elseif $event eq 'upload_image'}
        {include file="elib://admin/upload_artist_image.tpl"}
        {else}

        {if $artist->image neq ''}
        <img class="img-fluid" src="http://{$WEB_ROOT}{$PUBLIC_DIR}/uploads/mid_{$artist->image}" alt="" />
        {else}&nbsp;
        {/if}
    </div>
</div>


{/if}





</div>








{include file="elib://admin/admin_footer.tpl"}