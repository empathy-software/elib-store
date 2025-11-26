



<h2>Rename Artist</h2>

{include file="elib://comp_errors.tpl"}


<form method="post">
    <div class="form-group">
        <label for="forename">Forename</label>
        <input type="text" value="{$artist->forename}" class="form-control" name="forename" id="forename">
    </div>
    <div class="form-group">
        <label for="surname">Surname</label>
        <input type="text" value="{$artist->surname}" class="form-control" id="surname" name="surname">
    </div>
    <input type="hidden" name="id" value="{$artist->id}"/>
    <button type="submit" class="btn btn-primary" name="save">Save</button>
    <button type="submit" class="btn btn-primary" name="cancel">Cancel</button>
</form>




