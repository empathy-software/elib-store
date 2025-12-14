


{if sizeof($colours) > 0}
<div id="variants">


{section name=colour loop=$colours}
<div class="variant">

<p><strong>{$colours[colour].option_val}</strong></p>

<div class="img">
<img src="http://{$WEB_ROOT}{$PUBLIC_DIR}/{if $colours[colour].image eq ''}elib/blank.gif{else}uploads/mid_{$colours[colour].image}{/if}" alt="" />
</div>

<ul class="operations">
<li><a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/edit_colour/{$colours[colour].id}">Edit</a></li>
<li><a class="confirm" href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/product/delete_colour/{$colours[colour].id}">Delete</a></li>
</ul>


<p class="clear">&nbsp;</p>
</div>
{/section}
</div>

{else}
<p>No colour property found.</p>

{/if}
