

{if $error neq ''}
    <p>{$error}</p>
{/if}


<h2>Upload Image</h2>

<form method="post" enctype="multipart/form-data">
    <div class="mb-4">
        <label class="form-label" for="file">Choose file</label>
        <input type="file" class="form-control" id="file" name="file">
    </div>
    <div class="mb-4">
        <input type="hidden" name="id" value="{$product->id}" />
        <button type="submit" class="btn btn-sm btn-primary" name="upload">Upload</button>
        <button type="submit" class="btn btn-sm btn-primary" name="cancel">Cancel</button>
    </div>

</form>



