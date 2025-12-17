

<h2>Rename Category</h2>


{include file="elib://comp_errors.tpl"}


<form method="post" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label" for="name">Name</label>
        <input type="text" value="{$category->name}" class="form-control" name="name" id="name">
    </div>
    <div class="mb-3">
        <input type="hidden" name="id" value="{$category->id}" />
        <button type="submit" class="btn btn-sm btn-primary" name="save">Save</button>
        <button type="submit" class="btn btn-sm btn-primary" name="cancel">Cancel</button>
    </div>
</form>

