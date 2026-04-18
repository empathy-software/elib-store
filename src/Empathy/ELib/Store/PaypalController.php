<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\EController;
use Empathy\ELib\Storage\CategoryItem;
use Empathy\ELib\Storage\LineItem;
use Empathy\ELib\Storage\OrderItem;
use Empathy\ELib\Storage\PaypalTransactions;
use Empathy\ELib\Storage\ProductVariant;
use Empathy\ELib\ThirdParty\PaypalClass;
use Empathy\MVC\Config;
use Empathy\MVC\DI;
use Empathy\MVC\LogItem;
use Empathy\MVC\Model;
use Empathy\MVC\Session;

class PaypalController extends EController
{
    private function getPayPalURL(): mixed
    {
        $url = '';
        if (!defined('ELIB_USE_PAYPAL_SANDBOX')) {
            throw new \Exception('Do not know whether to use paypal sandbox.');
        }
        if (ELIB_USE_PAYPAL_SANDBOX) {
            $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        } else {
            $url = 'https://www.paypal.com/cgi-bin/webscr';
        }

        return $url;
    }

    public function success(): void
    {
        $c = new ShoppingCart();
        $c->emptyCart();
        $this->assignMessage('Thank you for your order');
    }

    public function cancel(): void
    {
        $this->assignMessage('The order was canceled');
    }


    private function writeLog(mixed $message, mixed $level = 'info'): void
    {
        $log = new LogItem(
            'paypal ipn',
            [],
            self::class,
            $level
        );
        $log->append('message', $message);
        $log->fire();
    }


    public function ipn(): void
    {
        $p = new PaypalClass();
        $p->ipn_log = true;
        $p->paypal_url = $this->getPayPalURL();

        if (!$p->validate_ipn()) {
            $this->writeLog('IPN validation failed');
            return;
        }

        $this->writeLog('Valid ipn data');

        $data = $p->ipn_data;
        $pt = Model::load(PaypalTransactions::class);

        $this->writeLog('Loaded transactions model');

        if (
            empty($data['invoice']) ||
            ($data['payment_status'] ?? '') !== 'Completed' ||
            ($data['receiver_email'] ?? '') !== $this->getBusiness()
        ) {
            $this->writeLog('Basic IPN checks failed');
            return;
        }

        $this->writeLog('Completed');

        if (empty($data['txn_id'])) {
            $this->writeLog('Missing txn_id');
            return;
        }

        if ($pt->txnExists($data['txn_id'])) {
            $this->writeLog('Duplicate txn_id: ' . $data['txn_id']);
            return;
        }

        $o = Model::load(OrderItem::class);
        if (!($o->loadByOrderId($data['invoice']))) {
            $this->writeLog('Invalid order id', 'error');
            return;
        }

        $this->writeLog('order: ' . json_encode([$data['invoice'], $o->id, $o->stamp]));

        $this->writeLog('amounts: ' . json_encode([(float) $data['mc_gross'], (float) $o->total, (float) $o->shipping]));

        if (
            (float)$data['mc_gross'] !== ((float)$o->total + (float)$o->shipping) ||
            ($data['mc_currency'] ?? '') !== 'PHP'
        ) {
            $this->writeLog('Amount or currency mismatch', 'error');
            return;
        }

        $this->writeLog('Total amount + currency');

        // TODO: enforce shipping country via PayPal `custom` + IPN validation

        $l = Model::load(LineItem::class);
        $items = $l->getOrderItems($o->id);

        // First pass: check all stock
        $stockProblem = false;

        foreach ($items as $item) {
            $v = Model::load(ProductVariant::class);
            $v->load($item['variant_id']);

            if ((int)$v->stock < (int)$item['quantity']) {
                $stockProblem = true;
                $this->writeLog(
                    'Out of stock for variant ' . $item['variant_id'] .
                    '. Needed ' . $item['quantity'] .
                    ', available ' . $v->stock,
                    'error'
                );
            }
        }

        if ($stockProblem) {
            // use a separate status for paid but needs attention
            $o->status = 3; // example: manual review / stock issue
            $o->save();

            $pt->storeTxn($data['txn_id']);
            $this->writeLog('Payment received but stock issue flagged');
            return;
        }

        // Second pass: decrement stock now we know everything is available
        foreach ($items as $item) {
            $v = Model::load(ProductVariant::class);
            $v->load($item['variant_id']);
            $v->stock -= (int)$item['quantity'];
            $v->save();
        }

        $o->status = 2;
        $o->save();

        $pt->storeTxn($data['txn_id']);
        $this->writeLog('Payment completed');
    }

    public function assignMessage(mixed $message): void
    {
        if ($message !== '') {
            $this->assign('message', $message);
        }
    }

    public function default_event(): void
    {
        $c = new ShoppingCart();
        if ($c->isEmpty()) {
            $this->redirect('paypal/cancel');
        }
        $items = $c->loadFromCart();

        $co = new Checkout($items);
        $invoice_no = $co->getInvoiceId();

        $o = Model::load(OrderItem::class);
        $o->load($co->getInvoiceNo());

        $products = [];

        //$product[0]['alias'] = 'Some product';
        //$product[0]['price'] = 1.99;
        //$product[0]['code'] = 23;

        $interface = 'https://'.Config::get('WEB_ROOT').Config::get('PUBLIC_DIR').'/paypal/';
        $message = '';
        $p = new PaypalClass();
        $p->ipn_log = false;
        $p->add_field('cmd', '_cart');
        $p->add_field('upload', '1');

        // address
        //        $p->add_field('first_name', $o->first_name);
        //        $p->add_field('last_name', $o->last_name);
        //        $p->add_field('address1', $o->address1);
        //        $p->add_field('address2', $o->address2);
        //        $p->add_field('city', $o->city);
        //        $p->add_field('state', $o->state);
        //        $p->add_field('zip', $o->zip);
        //        $p->add_field('country', $o->country);
        //        $p->add_field('address_override', 1);
        //        $p->add_field('no_shipping', 1);

        //$countries = \Empathy\ELib\Country\Country::build();
        $shippingCountry = Session::get('shipping_country') ? Session::get('shipping_country') : 'GB';
        $p->add_field('country', $shippingCountry);


        // shipping
        $ids = [];
        foreach ($items as $item) {
            array_push($ids, $item['id']);
        }
        $v = Model::load(ProductVariant::class);
        $cat_ids = $v->getCategories($ids);
        $cat = Model::load(CategoryItem::class);

        if ($o->country !== 'GB') {
            $intl = true;
        } else {
            $intl = false;
        }
        $sc = DI::getContainer()->get('ShippingCalculator');
        $shipping = $sc->getFee();

        $p->add_field('shipping_1', number_format($shipping, 2, '.', ''));
        $o->shipping = $shipping;
        $o->save();


        $i = 1;
        foreach ($items as $index => $item) {
            $p->add_field('item_name_'.$i, $item['name']);
            $p->add_field('amount_'.$i, $item['price']);
            $p->add_field('item_number_'.$i, $item['id']);
            $p->add_field('quantity_'.$i, $item['qty']);

            $o = explode(', ', $item['options']);
            $pr = explode(', ', $item['properties']);
            foreach ($o as $index => $item) {
                $p->add_field('os'.$index.'_'.$i, $o[$index]);
                $p->add_field('on'.$index.'_'.$i, $pr[$index]);
            }

            $i++;
        }

        //    $p->add_field('image_url', 'http://'.WEB_ROOT.PUBLIC_DIR.'/img/pier.png');
        $p->add_field('invoice', $invoice_no);
        $p->add_field('no_shipping', 0);
        $p->add_field('currency_code', 'PHP');
        $p->add_field('business', $this->getBusiness());

        $p->add_field('return', $interface.'success');
        $p->add_field('notify_url', $interface.'ipn');
        $p->add_field('cancel_return', $interface.'cancel');
        $p->paypal_url = $this->getPayPalURL();

        $this->assign('paypal_url', $p->paypal_url);
        $this->assign('fields', $p->fields);

        //$p->dump_fields();
        //exit();
    }

    protected function getBusiness(): mixed
    {
        return ELIB_PAYPAL_BUSINESS_EMAIL;
    }
}
