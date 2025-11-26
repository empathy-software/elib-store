

<h2>Rename Category</h2>


{include file="elib://comp_errors.tpl"}


<form method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" value="{$category->name}" class="form-control" name="name" id="name">
    </div>
    <input type="hidden" name="id" value="{$category->id}" />
    <button type="submit" class="btn btn-primary" name="save">Save</button>
    <button type="submit" class="btn btn-primary" name="cancel">Cancel</button>
</form>

