


{if sizeof($variants) > 0}
    <div class="variants flex-wrap row">
        {section name=variant loop=$variants}
            {assign var="class" value="col-lg-4"}
            {assign var="v" value=$variants[variant]}
            {assign var="showActions" value=true}


            {include file="elib://admin/products/variant.tpl"}
        {/section}
    </div>
{/if}
