

{include file="elib://comp_errors.tpl"}


<fieldset><legend>Edit Product Colour</legend>
<form action="" method="post" enctype="multipart/form-data">

<p>
<label>Colour</label>
<select name="colour">
{html_options options=$colours selected=$product_colour->property_option_id}
</select>
</p>

<p><label for="file">File</label>
<input type="file" name="file" /></p>


<p><label>&nbsp;</label>
<input type="hidden" name="id" value="{$product_colour->id}" />
<button type="submit" name="save_colour">Save</button></p>
</form>
</fieldset>


