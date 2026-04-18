<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\DI;
use Empathy\MVC\Entity;
use Empathy\MVC\Model;

/**
 * Base model for vendor records when using {@see \Empathy\MVC\DI} key <code>VendorModel</code>.
 */
abstract class VendorItem extends Entity
{
    public int $user_id = 0;

    public ?string $name = null;

    public ?string $verified = null;

    abstract public function getIDByUserID(int $user_id): int;

    public static function loadFromContainer(): self
    {
        $class = DI::getContainer()->get('VendorModel');
        if (!is_string($class) || !is_a($class, self::class, true)) {
            throw new \LogicException('VendorModel must be a class-string extending ' . self::class);
        }
        return Model::load($class);
    }
}
