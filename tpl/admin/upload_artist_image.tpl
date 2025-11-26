


{if $error neq ''}
 <p>{$error}</p>
{/if}


<form action="" method="post" enctype="multipart/form-data">
 <fieldset>
  <legend>Add Image</legend>
  <p>
   <label>File</label>
   <input type="file" id="file" name="file" accept="image/*" />
   <!-- <input type="file" id="file" name="file" /> -->
  </p>
  <p>
   <label>&nbsp;</label>
   <input type="hidden" name="id" value="{$artist->id}" />
   <button class="btn btn-sm btn-primary" type="submit" name="upload">Upload</button>
   <button class="btn btn-sm btn-primary" type="submit" name="cancel">Cancel</button>
  </p>
 </fieldset>
</form>

