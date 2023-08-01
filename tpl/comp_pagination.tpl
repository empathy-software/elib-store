
{if sizeof($p_nav) > 1}
    {assign var="lastPage" value=0}
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-end">
            <li class="page-item{if $page eq 1} disabled{/if}">
                <a class="page-link" href="?page={$page - 1}" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                    <span class="sr-only">Previous</span>
                </a>
            </li>
            {foreach from=$p_nav key=k item=v}
                <li class="page-item{if $v} disabled{/if}">
                    <a class="page-link" href="?page={$k}">
                        {$k}
                    </a>
                </li>
                {assign var="lastPage" value=$k}
            {/foreach}
            <li class="page-item{if $page eq $lastPage} disabled{/if}">
                <a class="page-link" href="?page={$page + 1}" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                    <span class="sr-only">Next</span>
                </a>
            </li>
        </ul>
    </nav>
{/if}