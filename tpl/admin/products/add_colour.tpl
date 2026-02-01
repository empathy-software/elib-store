


{include file="elib://comp_errors.tpl"}


<fieldset><legend>Add Product Colour</legend>
<form action="" method="post" enctype="multipart/form-data">

<p>
<label>Colour</label>
<select name="colour">
{html_options options=$colours selected=$product->getSoldInStore()}
</select>
</p>

<p><label for="file">File</label>
<input type="file" name="file" /></p>


<p><label>&nbsp;</label>
<input type="hidden" name="id" value="{$product->id}" />
<button type="submit" name="submit_colour">Save</button></p>
</form>
</fieldset>


