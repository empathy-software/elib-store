<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

class ProductVariantStatus
{
    public const CREATED = 0;
    public const AVAILABLE = 1;
    public const DELETED = 2;
    public const SOLD_OUT = 3;

    public static function getStatus(mixed $status): mixed {
        $status_text = '';
        switch ($status) {
            case self::CREATED:
                $status_text = 'Hidden';
                break;
            case self::AVAILABLE:
                $status_text = 'Available';
                break;
            case self::DELETED:
                $status_text = 'Deleted';
                break;
            case self::SOLD_OUT:
                $status_text = 'Sold Out';
                break;
            default:
                break;
        }

        return $status_text;
    }
}
