

<h2>Edit Biography</h2>

<form method="post">
    <div class="form-group">
        <label for="bio">Biography</label>
        <textarea class="form-control" id="bio" rows="10" name="bio">{$brand->about|escape}</textarea>
    </div>
    <input type="hidden" name="id" value="{$brand->id}" />
    <button type="submit" class="btn btn-primary" name="save">Save</button>
    <button type="submit" class="btn btn-primary" name="cancel">Cancel</button>
</form>

