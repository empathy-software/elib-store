<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\EController;
use Empathy\MVC\DI;

class VendorController extends EController
{
    public function __construct($boot)
    {
        parent::__construct($boot);
        $user = DI::getContainer()->get('CurrentUser');
        if (!$user->isLoggedIn() || !$user->isAuthLevel(Access::VENDOR)) {
            $this->redirect('');
        }
    }

}
