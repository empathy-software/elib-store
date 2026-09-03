<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\AdminController as AController;

class AdminController extends AController
{
    protected function clearCache(): void
    {
        $cache = $this->stash->get('cache');
        if (is_object($cache) && method_exists($cache, 'clear')) {
            $cache->clear();
        }
    }
}
