<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

/**
 * Auth level constants for store / vendor UI (used with CurrentUser::isAuthLevel()).
 */
final class Access
{
    /** Vendor role (must align with values stored in {@see \Empathy\ELib\Storage\UserItem::$auth}). */
    public const int VENDOR = 4;
}
