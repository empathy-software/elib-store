<?php

namespace Empathy\ELib\Store;

use Empathy\MVC\DI;
use Empathy\MVC\LogItem;
use Empathy\MVC\Model;
use Empathy\ELib\EController;
use Empathy\ELib\ThirdParty\PaypalClass;
use Empathy\MVC\Config;
use Empathy\ELib\Storage\OrderItem;
use Empathy\ELib\Storage\ProductVariant;
use Empathy\ELib\Storage\CategoryItem;
use Empathy\ELib\Storage\PaypalTransactions;
use Empathy\MVC\Session;


class PaypalController extends EController
{

    private function getPayPalURL()
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

    public function success()
    {
        $this->assignMessage('Thank you for your order');
    }

    public function cancel()
    {
        $this->assignMessage('The order was canceled');
    }


    private function writeLog($message)
    {
        $log = new LogItem(
            'paypal ipn',
            [],
            self::class,
            'info'
        );
        $log->append('message', $message);
        $log->fire();
    }


    public function ipn()
    {
        $p = new PaypalClass();
        $p->ipn_log = true;
        $p->paypal_url = $this->getPayPalURL();

        if ($p->validate_ipn()) {

            $this->writeLog('Valid ipn data');
            $data = $p->ipn_data;
            $pt = Model::load(PaypalTransactions::class);

            $this->writeLog('Loaded transactions model');

            if (
                !empty($data['invoice']) &&
                $data['payment_status'] === 'Completed' &&
                $data['receiver_email'] === $this->getBusiness()
            ) {

                $this->writeLog('Completed');

                $o = Model::load(OrderItem::class);
                $o->load(ltrim($data['invoice'], 'OV'));

                // Check amount + currency
                if (
                    (float) $data['mc_gross'] === ((float) $o->total + (float) $o->shipping) &&
                    $data['mc_currency'] == 'PHP'
                ) {

                    $this->writeLog('total amount + currency');

                    // Check txn_id not already used
                    if (!$pt->txnExists($data['txn_id'])) {
                        $o->status = 2;
                        $o->save();

                        $pt->storeTxn($data['txn_id']);
                        $this->writeLog('Payment completed');
                    }
                }
            }
        }


        // decrement stock
//        $v = Model::load(ProductVariant::class);
//        $v->load($l->variant_id);
//        $v->stock--;
//        $v->save();

//        if ($p->validate_ipn()) {
//            if(isset($p->ipn_data['invoice']) && is_numeric($p->ipn_data['invoice'])
//               && 'Completed' == $p->ipn_data['payment_status'])
//            {
//                $o = Model::load(OrderItem::class);
//                $o->id = $p->ipn_data['invoice'];
//                $o->load();
//                $o->status = 2;
//                $o->save();
//            }
//            $this->writeLog($p->ipn_data);
//        }
    }

    public function assignMessage($message)
    {
        if ($message != '') {
            $this->presenter->assign('message', $message);
        }
    }

    public function default_event(): void
    {
        $c = new ShoppingCart();
        if ($c->isEmpty()) {
            $this->redirect('paypal/cancel');
        }
        $items = $c->loadFromCart();

        $co = new Checkout($items, $this);
        $invoice_no = $co->getInvoiceNo();

        $o = Model::load(OrderItem::class);
        $o->id = $invoice_no;
        $o->load($o->id);

        $products = array();

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
        $shippingCountry = Session::get('shipping_country') ? Session::get('shipping_country'): 'GB';
        $p->add_field('country', $shippingCountry);


        // shipping
        $ids = array();
        foreach ($items as $item) {
            array_push($ids, $item['id']);
        }
        $v = Model::load(ProductVariant::class);
        $cat_ids = $v->getCategories($ids);
        $cat = Model::load(CategoryItem::class);

        if ($o->country != 'GB') {
            $intl = true;
        } else {
            $intl = false;
        }
        $sc = DI::getContainer()->get('ShippingCalculator');
        $shipping = $sc->getFee();

        $p->add_field('shipping_1', $shipping);
        $o->shipping = $shipping;
        $o->save();

        $i = 1;
        foreach ($items as $index => $item) {
            $p->add_field('item_name_'.$i, $item['name']);
            $p->add_field('amount_'.$i, $item['price']);
            $p->add_field('item_number_'.$i, $this->getItemNumber($item['id']));
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
        $p->add_field('invoice', $this->getInvoiceNumber($invoice_no));
        $p->add_field('no_shipping', 1);
        $p->add_field('currency_code', 'PHP');
        $p->add_field('business', $this->getBusiness());

        $p->add_field('return', $interface.'success');
        $p->add_field('notify_url', $interface.'ipn');
        $p->add_field('cancel_return', $interface.'cancel');
        $p->paypal_url = $this->getPayPalURL();

        $this->presenter->assign('paypal_url', $p->paypal_url);
        $this->presenter->assign('fields', $p->fields);

        //$p->dump_fields();
        //exit();
    }

    protected function getBusiness()
    {
        return ELIB_PAYPAL_BUSINESS_EMAIL;
    }

    protected function getItemNumber($id)
    {
        return $id;
    }

    protected function getInvoiceNumber($id)
    {
        return $id;
    }

    protected function getShipping()
    {
        return 0;
    }

}
