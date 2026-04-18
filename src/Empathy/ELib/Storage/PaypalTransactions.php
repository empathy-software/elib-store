<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;

class PaypalTransactions extends Entity
{
    public const TABLE = 'paypal_transactions';

    public int $id;
    public $txn_id;
    public $created_at;

    public function txnExists($txnId)
    {
        $sql = 'SELECT COUNT(*) FROM paypal_transactions WHERE txn_id = ?';
        $result = $this->query($sql, 'Could not check for existing transaction', [$txnId]);
        return $result->fetchColumn() > 0;
    }

    public function storeTxn($txnId)
    {
        $sql = 'INSERT INTO paypal_transactions (txn_id) VALUES (?)';
        $this->query($sql, 'Could not insert transaction', [$txnId]);
    }
}
