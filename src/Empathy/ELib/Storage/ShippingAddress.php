<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;
use Empathy\MVC\Model;
use Empathy\MVC\Validate;

class ShippingAddress extends Entity
{
    public const TABLE = 'shipping_address';

    public int $id;

    public int $user_id = 0;

    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $address1 = null;
    public ?string $address2 = null;
    public ?string $city = null;
    public ?string $state = null;
    public ?string $zip = null;
    public ?string $country = null;

    public int $default_address = 0;

    public function validates(): void
    {
        $this->doValType(Validate::TEXT, 'first_name', $this->first_name, false);
        $this->doValType(Validate::TEXT, 'last_name', $this->last_name, false);
        $this->doValType(Validate::TEXT, 'address1', $this->address1, false);
        $this->doValType(Validate::TEXT, 'address2', $this->address2, true);
        $this->doValType(Validate::TEXT, 'city', $this->city, false);
        $this->doValType(Validate::TEXT, 'state', $this->state, false);
        $this->doValType(Validate::TEXT, 'zip', $this->zip, false);
        $this->doValType(Validate::TEXT, 'country', $this->country, false);
    }

    public function setDefault(mixed $user_id, mixed $address_id): void
    {
        $sql = 'SELECT id FROM '.Model::getTable(self::class).' WHERE user_id = ?';
        $error = 'Could not get all shipping addresses for user.';
        $result = $this->query($sql, $error, [$user_id]);

        $addresses = [];
        foreach ($result as $row) {
            array_push($addresses, $row['id']);
        }

        if (in_array($address_id, $addresses, true)) {
            $sql = 'UPDATE '.Model::getTable(self::class).' SET default_address = 0 WHERE user_id = ?';
            $error = 'Could not wipe defaults.';
            $this->query($sql, $error, [$user_id]);
            $sql = 'UPDATE '.Model::getTable(self::class).' SET default_address = 1 WHERE id = ?';
            $error = 'Could not set new default';
            $this->query($sql, $error, [$address_id]);
        }
    }
}
