<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\AuthedController;
use Empathy\ELib\EController;
use Empathy\ELib\Storage\CategoryItem;
use Empathy\ELib\Storage\CategoryProperty;
use Empathy\ELib\Storage\ProductItem;
use Empathy\ELib\Storage\ProductVariant;
use Empathy\ELib\Storage\ProductVariantPropertyOption;
use Empathy\ELib\Storage\Property;
use Empathy\ELib\Storage\ShippingAddress;
use Empathy\ELib\Storage\VendorItem;
use Empathy\MVC\DI;
use Empathy\MVC\Model;
use Empathy\MVC\Session;

//class StoreControllerLite extends AuthedController
class StoreControllerLite extends EController
{
    protected mixed $pages;
    protected mixed $vendor_lock;

    public function __construct(mixed $boot)
    {
        parent::__construct($boot);
        $this->assign('cart_items', ShoppingCart::getTotalItems());

        // vendor lock
        $vendor_lock = Session::get('vendor_lock');
        if (is_numeric($vendor_lock)) {
            $this->vendor_lock = $vendor_lock;
            $this->assign('vlock', $this->vendor_lock);
        }
    }

    public function filterInt(mixed $name): mixed
    {
        if (isset($_GET[$name])) {
            return (int) $_GET[$name];
        } else {
            return 0;
        }
    }

    public function default_event(): void
    {
        $this->setTemplate('elib://store_category.tpl');
    }

    public function minimalLayout(): void
    {
        //        if (0 && CurrentUser::loggedIn()) {
        //            $ui_array = array('page', 'vendor_id', 'id');
        //            $this->loadUIVars('ui_blog', $ui_array);
        //        } else {
        //            $page = $this->filterInt('page');
        //            $vendor_id = $this->filterInt('vendor_id');
        //            $category_id = $this->filterInt('id');
        //        }

        $category_id = $_GET['category_id'] ?? 0;

        if (isset($_GET['order_by_price'])) {
            Session::setUISetting('ui_store', 'order_by_recent', false);
        }
        if (isset($_GET['order_by_recent'])) {
            Session::setUISetting('ui_store', 'order_by_price', false);
        }

        $this->loadUIVars('ui_store', ['order_by_recent', 'order_by_price', 'page', 'brands']);


        if (!isset($_GET['brands'])) {
            $_GET['brands'] = [1];
        }

        $brands_available = [
            1 => ['name' => 'A.CE', 'set' => is_array($_GET['brands']) ? in_array(1, $_GET['brands'], true) : 0],
            2 => ['name' => 'VEIL', 'set' => is_array($_GET['brands']) ? in_array(2, $_GET['brands'], true) : 0],
        ];
        $this->assign('brands_available', $brands_available);

        $page = $this->filterInt('page');

        if (
            isset($_GET['order_by_price']) &&
            (
                $_GET['order_by_price'] === 'asc' ||
                $_GET['order_by_price'] === 'desc'
            )
        ) {
            $orderByPrice = $_GET['order_by_price'];
        }
        if (
            isset($_GET['order_by_recent']) &&
            (
                $_GET['order_by_recent'] === 'asc' ||
                $_GET['order_by_recent'] === 'desc'
            )
        ) {
            $orderByRecent = $_GET['order_by_recent'];
        }

        $this->assign('order_by_price', $orderByPrice ?? 'desc');
        $this->assign('order_by_recent', $orderByRecent ?? 'desc');


        if (!isset($_GET['page']) || $_GET['page'] === '') {
            $_GET['page'] = 1;
        }

        // vendor lock
        if (isset($this->vendor_lock)) {
            $_GET['vendor_id'] = $this->vendor_lock;
        }

        $cats = ProductsLayout::getTopCats();

        //        $top_cat = array(
        //            'id' => 0,
        //            'name' => 'All',
        //            'hidden' => false
        //        );
        //        array_unshift($cats, $top_cat);

        $current_cat_id = 0;
        foreach ($cats as $c) {
            if (isset($c['sub_cats'])) {
                foreach ($c['sub_cats'] as $sub) {
                    if ($sub['id'] === $category_id) {
                        $current_cat_id = $sub['id'];
                    }
                }
            }
            if ($c['id'] === $category_id) {
                //$this->assign('current_category', $c['name']);
                $current_cat_id = $c['id'];
            }
        }

        $this->assign('current_cat_id', $current_cat_id);
        $this->assign('top_cats', $cats);

        $c = Model::load(CategoryItem::class);
        $descendants = [];
        $c->buildDescendantIDs($category_id, $descendants);


        if (!isset($_GET['vendor_id'])) {
            $_GET['vendor_id'] = 0;
        } else {
            $_GET['vendor_id'] = (int) $_GET['vendor_id'];
        }

        $p = Model::load(ProductItem::class);

        $status = '('
            .\Empathy\ELib\Storage\ProductItemStatus::AVAILABLE.', '
            .\Empathy\ELib\Storage\ProductItemStatus::SOLD_OUT
            .')';
        $sql = ' WHERE status IN'.$status;
        $params = [];
        if ($_GET['vendor_id'] > 0) {
            $sql .= ' AND vendor_id = ?';
            $params[] = $_GET['vendor_id'];
        }

        if ($category_id !== 0) {
            $descendantsString = $p->buildUnionString($descendants);
            $sql .= ' AND category_id IN ' . $descendantsString[0];
            $params = array_merge($params, $descendantsString[1]);
        }

        if (is_array($_GET['brands']) && count($_GET['brands']) > 0) {
            $brandIds = [];
            foreach ($_GET['brands'] as $bid) {
                if (is_numeric($bid)) {
                    $brandIds[] = (int) $bid;
                }
            }
            if ($brandIds !== []) {
                $brands = $p->buildUnionString($brandIds);
                $sql .= ' AND brand_id IN ' . $brands[0];
                $params = array_merge($params, $brands[1]);
            }
        }

        $sql .= ' AND vendor_verified = 1';

        $order = [];
        if (isset($orderByRecent)) {
            $order[] = 'id ' .  ($orderByRecent === 'desc' ? 'desc' : 'asc');
        }

        if (isset($orderByPrice)) {
            $order[] = 'min_price ' . ($orderByPrice === 'asc' ? 'asc' : 'desc');
        }

        if (count($order) > 0) {
            $sql .= ' ORDER BY ' . implode(', ', $order);
        } else {
            $sql .= ' ORDER BY id DESC';
        }

        if ($_GET['page'] < 1) {
            $_GET['page'] = 1;
        }

        $per_page = 12;
        $products = $p->getAllCustomPaginate($sql, $_GET['page'], $per_page, $params);

        foreach ($products as &$product) {
            $p->load($product['id']);
            $images = $p->getImages();
            $product['image'] = $images[0]['image'];
            $product['stock'] = $p->getStock();
        }

        $p_nav = $p->getPaginatePages($sql, $_GET['page'], $per_page, $params);

        $this->pages = $p_nav;

        $this->assign('products', $products);
        $this->assign('p_nav', $p_nav);
        $this->assign('page', $_GET['page']);

        $this->assign('vendor_id', $_GET['vendor_id']);
        if (isset($_GET['id'])) {
            $this->assign('category_id', $_GET['id']);
        }
    }

    public function addProductToCart(mixed $product_id): void
    {
        $options = [];
        $variant_id = 0;
        if (isset($_POST['property'])) {
            foreach ($_POST['property'] as $option) {
                array_push($options, $option);
            }
        }

        $v = Model::load(ProductVariant::class);
        $p = Model::load(ProductItem::class);

        if (sizeof($options) > 0) {
            $variant_id = $v->findVariant($options, $product_id);
        } else {
            $sql = ' WHERE product_id = ? LIMIT 0, 1';
            $variant = $v->getAllCustom($sql, [$product_id]);
            if (sizeof($variant) > 0) {
                $variant_id = $variant[0]['id'];
            }
        }

        if (is_numeric($variant_id) && $variant_id > 0) {
            $sc = new ShoppingCart();
            $cartData = $sc->loadFromCart();
            $cartItem = array_find($cartData, fn ($item) => $item['id'] === $variant_id);

            $notAdded = false;
            $v->load($variant_id);
            $p->load($v->product_id);
            $stock = (int) $p->getStock();
            if ($stock === 0 || ($cartItem && $stock < $cartItem['qty'] + 1)) {
                $notAdded = true;
            } else {
                $sc->add($variant_id, 1);
                // set vendor lock
                //                if (Session::get('vendor_lock') == false) {
                //                    $v = Model::load(ProductVariant::class);
                //                    $v->load($variant_id);
                //                    $p = Model::load(ProductItem::class);
                //                    $p->id = $v->product_id;
                //                    $p->load($p->id);
                //                    Session::set('vendor_lock', $p->vendor_id);
                //                }
            }
            Session::set('cart_not_added', $notAdded);
            $this->redirect('store/cart');
        }
    }

    public function minimalProductView(): void
    {
        $this->setTemplate('store_product.tpl');
        $p = Model::load(ProductItem::class);
        $p->load($this->filterInt('id'));

        $vendorModel = DI::getContainer()->get('VendorModel');
        if (is_string($vendorModel) && $vendorModel !== '' && $p->vendor_id !== null && $p->vendor_id > 0) {
            $v = VendorItem::loadFromContainer();
            $v->load($p->vendor_id);
            $this->assign('vendor', $v);
        }

        if (isset($_POST['add'])) {
            $this->addProductToCart($p->id);
        }

        //$this->getPropertiesAndOptions($p->id, 0);
        $this->getPropertiesAndOptions($p, 0);

        // breadcrumb
        $c = Model::load(CategoryItem::class);
        $bc = [];
        $c->buildBreadCrumb($p->category_id, $bc);
        $bc = array_reverse($bc);
        $this->assign('breadcrumb', $bc);
        $this->assign('vendor_id', $p->vendor_id);
        $this->assign('product', $p);
        $this->assign('colours', []);

        $cart = new ShoppingCart();
        $cartQty = $cart->getQtyByProductId($p->id);

        if ($p->getStock() < 1 || $cartQty >= $p->getStock()) {
            $this->assign('add_disabled', true);
        }
    }

    public function cart(): void
    {
        $sc = DI::getContainer()->get('ShippingCalculator');

        if (isset($_GET['get_shipping']) && $_GET['get_shipping']) {
            $sc = DI::getContainer()->get('ShippingCalculator');
            header('Content-type: application/json');
            echo json_encode($sc->getFee());
            exit();
        }
        $variant = Model::load(ProductVariant::class);
        $product = Model::load(ProductItem::class);

        $countries = \Empathy\ELib\Country\Country::build();
        $shippingCountry = Session::get('shipping_country') ? Session::get('shipping_country') : 'GB';

        if (isset($_GET['shipping_country']) && in_array($_GET['shipping_country'], array_keys($countries), true)) {

            if ($shippingCountry !== $_GET['shipping_country']) {
                Session::set('shipping_country', $_GET['shipping_country']);
                $this->redirect('store/cart');
                return;
            }

            if (isset($_GET['checkout']) && $_GET['checkout'] === '1') {
                $this->redirect('store/checkout');
                return;
            }
        }

        $this->setTemplate('cart.tpl');
        $c = new ShoppingCart();

        if (isset($_POST['update'])) {
            $maxQuantitiesSet = false;
            $autoRemoved = false;

            foreach ($_POST['qty'] as $v => $qty) {
                if (is_numeric($qty) && $qty > 0) {
                    $variant->load($v);
                    $product->load($variant->product_id);
                    $stock = (int) $product->getStock();
                    if ($stock === 0) {
                        $autoRemoved = true;
                        $c->remove($v);
                        break;
                    } elseif ($stock < $qty) {
                        $maxQuantitiesSet = true;
                        $qty = $stock;
                    }
                    $c->update($v, $qty);
                } elseif (is_numeric($qty) && $qty === 0) {
                    $c->remove($v);

                    // vendor locking
                    //if ($c->isEmpty()) {
                    //    Session::clear('vendor_lock');
                    //}
                }
            }
            Session::set('cart_max_quantities_set', $maxQuantitiesSet);
            Session::set('cart_auto_removed', $autoRemoved);
            $this->redirect('store/cart');
            return;
        }

        $items = $c->loadFromCart();
        if (sizeof($items) > 0) {
            $shipping = $sc->getFee();
            $this->assign('shipping', $shipping);
            $this->assign('total', $c->calcTotal($items) + $shipping);
            $this->assign('items', $items);
        }

        if (isset($this->vendor_lock)) {
            $this->assign('vendor_id', $this->vendor_lock);
        }

        $this->assign('last_cat', Session::get('last_cat'));
        $this->assign('shipping_country', $shippingCountry);
        $this->assign('countries', $countries);

        $max_quantities_set = Session::get('cart_max_quantities_set');
        if ($max_quantities_set) {
            $this->assign('max_quantities_set', $max_quantities_set);
            Session::clear('cart_max_quantities_set');
        }
        $auto_removed = Session::get('cart_auto_removed');
        if ($auto_removed) {
            $this->assign('auto_removed', $auto_removed);
            Session::clear('cart_auto_removed');
        }
        $not_added = Session::get('cart_not_added');
        if ($not_added) {
            $this->assign('not_added', $not_added);
            Session::clear('cart_not_added');
        }
    }

    public function checkout(): void
    {
        //        $this->setTemplate('checkout.tpl');
        //        $s = Model::load(ShippingAddress::class);
        //
        //        $sql = ' WHERE user_id = ? ORDER BY default_address DESC';
        //        $addresses = $s->getAllCustom($sql, [DI::getContainer()->get('CurrentUser')->getUserID()]);
        //
        //        $this->assign('addresses', $addresses);
        //
        //        if (isset($_GET['checkout'])) {
        //            Session::set('shipping_address_id', $_GET['shipping_address_id']);
        //            $this->redirect('paypal/paypal');
        //        }


        Session::set('shipping_address_id', 0);
        $this->redirect('paypal/paypal');
    }

    // taken from product admin (variant properties)
    public function getPropertiesAndOptions(mixed $p, mixed $colours): void
    {
        /*
          $v = new ProductVariant($this);
          $v->id = $_GET['id'];
          $v->load(ProductVariant::$table);
        */

        //$p = Model::load('ProductItem');
        //$p->id = $product_id;
        //$p->load();

        $c = Model::load(CategoryItem::class);
        $cats = $c->getAncestorIds($p->category_id, []);

        $cp = Model::load(CategoryProperty::class);

        array_push($cats, $p->category_id);
        $props = $cp->getPropertiesByCategory($cats);

        if (!$colours && $p->category_id !== 8) {
            array_push($props, 2); // always allow colour property
        }

        //    $this->assign('product', $p);
        //$this->assign('variant', $v);

        $opts = [];
        $pv = Model::load(ProductVariantPropertyOption::class);
        $opts = $pv->buildUnionString($pv->getActiveOptions($p->id));

        if (sizeof($props) > 0) {
            $property = Model::load(Property::class);
            $properties = $property->getAllWithOptionsForProduct($props, $opts);
            $this->assign('properties', $properties);

            /*
              $pv = new ProductVariantPropertyOption($this);
              $sql = ' WHERE product_variant_id = '.$_GET['id'];
              $options = $pv->getAllCustom(ProductVariantPropertyOption::$table, $sql);
              $o = array();
              foreach ($options as $index => $value) {
              array_push($o, $value['property_option_id']);
              }
              $this->assign('options', $o);
            */

        }
    }
}
