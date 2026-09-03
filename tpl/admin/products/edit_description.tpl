

<h2>Edit Category Description</h2>


{include file="elib://comp_errors.tpl"}


<form method="post" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label" for="description">Description</label>
        <input type="text" value="{$category->description|escape}" class="form-control" name="description" id="description" maxlength="2000">
    </div>
    <div class="mb-3">
        <input type="hidden" name="id" value="{$category->id}" />
        <button type="submit" class="btn btn-sm btn-primary" name="save">Save</button>
        <button type="submit" class="btn btn-sm btn-primary" name="cancel">Cancel</button>
    </div>
</form>
