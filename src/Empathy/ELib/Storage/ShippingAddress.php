<?php

namespace Empathy\ELib\Storage;

use Empathy\MVC\Model;
use Empathy\MVC\Entity;
use Empathy\MVC\Validate;


class ShippingAddress extends Entity
{
    const TABLE = 'shipping_address';

    public $id;
    public $user_id;
    public $first_name;
    public $last_name;
    public $address1;
    public $address2;
    public $city;
    public $state;
    public $zip;
    public $country;
    public $default_address;

    public function validates()
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

    public function setDefault($user_id, $address_id)
    {
        $sql = 'SELECT id FROM '.Model::getTable(self::class).' WHERE user_id = ?';
        $error = 'Could not get all shipping addresses for user.';
        $result = $this->query($sql, $error, [$user_id]);

        $addresses = array();
        foreach ($result as $row) {
            array_push($addresses, $row['id']);
        }

        if (in_array($address_id, $addresses)) {
            $sql = 'UPDATE '.Model::getTable(self::class).' SET default_address = 0 WHERE user_id = ?';
            $error = 'Could not wipe defaults.';
            $this->query($sql, $error, [$user_id]);
            $sql = 'UPDATE '.Model::getTable(self::class).' SET default_address = 1 WHERE id = ?';
            $error = 'Could not set new default';
            $this->query($sql, $error, [$address_id]);
        }
    }
}
