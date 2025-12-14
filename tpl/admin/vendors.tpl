{include file="elib:/admin/admin_header.tpl"}


<div class="grey">

{if isset($vendors) and sizeof($vendors) < 1}
<p>Nothing to display.</p>
{else}
<table>
<tr>

<th>User ID</th>
<th>Username</th>
<th>Active</th>
<th>Real Name</th>
<th>email</th>

<th>Location</th>
<th>Registered</th>
<th>Vendor Name</th>
<th>&nbsp;</th>

</tr>
{section name=vendor loop=$vendors}
<tr class="{cycle values="alt," }">

<td class="id">{$vendors[vendor].user_id}</td>
<td>{$vendors[vendor].username}</td>
<td>{if $vendors[vendor].active}Yes{else}No{/if}</td>
<td>{$vendors[vendor].first_name} {$vendors[vendor].last_name}</td>
<td>{$vendors[vendor].email}</td>
<td>{$vendors[vendor].city}</td>
<td>{$vendors[vendor].registered|date_format:"%d/%m/%y"}</td>
{*<td>{$vendors[vendor].registered|date_format:"%d/%m/%y @ %T"}</td>*}
<td>{$vendors[vendor].name}</td>
<td>
<form class="confirm" action="" method="post">
<button {if $vendors[vendor].verified != ''}disabled="disabled"{/if} type="submit" name="verify">Verify</button>
<input type="hidden" name="vendor_id" value="{$vendors[vendor].vendor_id}" />
</form>
</td>

</tr>
{/section}

</table>
{/if}

{if isset($p_nav) and sizeof($p_nav) > 1}
<div id="p_nav">
<p>
{foreach from=$p_nav key=k item=v}
{if $v eq 1}<span>{$k}</span>
{else}
<a href="http://{$WEB_ROOT}{$PUBLIC_DIR}/admin/blog/?page={$k}">{$k}</a>
{/if}
{/foreach}
</p>
</div>
{else}
<p>&nbsp;</p>
{/if}



</div>



{include file="elib:/admin/admin_footer.tpl"}