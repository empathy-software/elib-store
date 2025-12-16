
<h2 class="mt-4 mb-4">Variants</h2>

{if sizeof($variants) > 0}
    <div class="variants flex-wrap row g-15">
        {section name=variant loop=$variants}
            {assign var="class" value="col-lg-4"}
            {assign var="v" value=$variants[variant]}
            {assign var="showActions" value=true}


            {include file="elib://admin/products/variant.tpl"}
        {/section}
    </div>
{/if}
