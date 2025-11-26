



{if $error neq ''}
    <p>{$error}</p>
{/if}


<h2>Upload Varaint Image</h2>

<form method="post" enctype="multipart/form-data">
    <div class="form-group custom-file">
        <input type="file" class="custom-file-input" id="file" name="file">
        <label class="custom-file-label" for="file">Choose file</label>
    </div>
    <input type="hidden" name="id" value="{$variant->id}" />
    <button type="submit" class="btn btn-primary" name="upload">Upload</button>
    <button type="submit" class="btn btn-primary" name="cancel">Cancel</button>
</form>

