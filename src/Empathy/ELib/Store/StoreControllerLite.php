<?php

namespace Empathy\ELib\Store;

use Empathy\ELib\Model,
    Empathy\ELib\AuthedController,
    Empathy\ELib\EController,
    Empathy\ELib\User\CurrentUser,
    Empathy\MVC\Session;



//class StoreControllerLite extends AuthedController
class StoreControllerLite extends EController
{
    protected $pages;
    protected $vendor_lock;

    public function __construct($boot)
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

    public function filterInt($name)
    {
        if (isset($_GET[$name])) {
            return (int) $_GET[$name];
        } else {
            return 0;
        }
    }

    public function default_event()
    {
        $this->setTemplate('elib://store_category.tpl');
    }

    public function minimalLayout()
    {
 

        if (0 && CurrentUser::loggedIn()) {
            $ui_array = array('page', 'vendor_id', 'id');
            $this->loadUIVars('ui_blog', $ui_array);
        } else {
            $page = $this->filterInt('page');
            $vendor_id = $this->filterInt('vendor_id');
            $category_id = $this->filterInt('id');
        }

    

        if (!isset($_GET['page']) || $_GET['page'] == '') {
            $_GET['page'] = 1;
        }

        // vendor lock
        if (isset($this->vendor_lock)) {
            $_GET['vendor_id'] = $this->vendor_lock;
        }

        $cats = ProductsLayout::getTopCats();


        $top_cat = array(
            'id' => 0,
            'name' => 'All',
            'hidden' => false
        );
        array_unshift($cats, $top_cat);
    
        foreach ($cats as $c) {
            if ($c['id'] == $category_id) {

                $this->assign('current_category', $c['name']);
            }
        }
        $this->assign('top_cats', $cats);

        $c = Model::load('CategoryItem');
        $descendants = array();
        $c->buildDescendantIDs($category_id, $descendants);


        if (!isset($_GET['vendor_id'])) {
            $_GET['vendor_id'] = 0;
        } else {
            $_GET['vendor_id'] = (int) $_GET['vendor_id'];
        }



        $p = Model::load('ProductItem');



        $status = '('
            .\Empathy\ELib\Storage\ProductItemStatus::AVAILABLE.', '
            .\Empathy\ELib\Storage\ProductItemStatus::SOLD_OUT
            .')';
        $sql = ' WHERE status IN'.$status;
        if ($_GET['vendor_id'] > 0) {
            $sql .= ' AND vendor_id = '.$_GET['vendor_id'];
        }


        $sql .= ' AND category_id IN'.$p->buildUnionString($descendants);

        $sql .= ' AND vendor_verified = 1';

        $sql .= ' ORDER BY id DESC';

        if ($_GET['page'] < 1) {
            $_GET['page'] = 1;
        }


        $per_page = 8;
        $products = $p->getAllCustomPaginate(Model::getTable('ProductItem'), $sql, $_GET['page'], $per_page);
        $p_nav = $p->getPaginatePages(Model::getTable('ProductItem'), $sql, $_GET['page'], $per_page);

        $this->pages = $p_nav;

        $this->assign('products', $products);
        $this->assign('p_nav', $p_nav);

        $this->assign('vendor_id', $_GET['vendor_id']);
        if (isset($_GET['id'])) {
            $this->assign('category_id', $_GET['id']);
        }
    }

    public function addProductToCart($product_id)
    {
        $options = array();
        if (isset($_POST['property'])) {
            foreach ($_POST['property'] as $option) {
                array_push($options, $option);
            }
        }

        if (sizeof($options) > 0) {
            $v = Model::load('ProductVariant');
            $variant_id = $v->findVariant($options, $product_id);
        } else {
            $sql = ' WHERE product_id = '.$prodcut_id.' LIMIT 0, 1';
            $variant = $v->getAllCustom(Model::getTable('ProductVariant'), $sql);
            if (sizeof($variant) > 0) {
                $variant_id = $variant[0]['id'];
            }
        }

        if (is_numeric($variant_id) && $variant_id > 0) {
            $sc = new ShoppingCart();
            $sc->add($variant_id, 1);

            // set vendor lock
            if (Session::get('vendor_lock') == false) {
                $v = Model::load('ProductVariant');
                $v->id = $variant_id;
                $v->load();
                $p = Model::load('ProductItem');
                $p->id = $v->product_id;
                $p->load();
                Session::set('vendor_lock', $p->vendor_id);
            }

            $this->redirect('store/cart');
        }
    }

    public function minimalProductView()
    {
        $this->setTemplate('store_product.tpl');
        $p = Model::load('ProductItem');
        $p->id = $this->filterInt('id');
        $p->load();

        $v = Model::load('Vendor');
        $v->id = $p->vendor_id;
        $v->load();

        if (isset($_POST['add'])) {
            $this->addProductToCart($p->id);
        }

        //$this->getPropertiesAndOptions($p->id, 0);
        $this->getPropertiesAndOptions($p, 0);

        // breadcrumb
        $c = Model::load('CategoryItem');
        $bc = array();
        $c->buildBreadCrumb($p->category_id, $bc);
        $bc = array_reverse($bc);
        $this->assign('breadcrumb', $bc);

        $this->assign('vendor_id', $p->vendor_id);
        $this->assign('product', $p);
        $this->assign('vendor', $v);

    }

    public function cart()
    {
        $this->setTemplate('cart.tpl');
        $c = new ShoppingCart();

        if (isset($_POST['update'])) {
            foreach ($_POST['qty'] as $v => $qty) {
                if (is_numeric($qty) && $qty > 0) {
                    $c->update($v, $qty);
                } elseif (is_numeric($qty) && $qty == 0) {
                    $c->remove($v);

                    // vendor locking
                    if ($c->isEmpty()) {
                        Session::clear('vendor_lock');
                    }
                }
            }
            $this->redirect('store/cart');
        } elseif (isset($_POST['checkout'])) {
            $this->redirect('store/checkout');
        }

        $items = $c->loadFromCart($this);

        if (sizeof($items) > 0) {
            $ids = array();
            foreach ($items as $item) {
                array_push($ids, $item['id']);
            }

            $v = Model::load('ProductVariant');
            $cat_ids = $v->getCategories($ids);
            $cat = Model::load('CategoryItem');
            $calc = new ShippingCalculator($c->calcTotal($items), $cat_ids, $cat, sizeof($items), false);
            $shipping = $calc->getFee();
            $shipping = 0;
            $this->assign('shipping', $shipping);
            $this->assign('total', $c->calcTotal($items) + $shipping);
            $this->assign('items', $items);
        }

        if (isset($this->vendor_lock)) {
            $this->assign('vendor_id', $this->vendor_lock);
        }

        $this->assign('last_cat', Session::get('last_cat'));
    }

    public function checkout()
    {
        $this->setTemplate('checkout.tpl');
        $s = Model::load('ShippingAddress');

        $sql = ' WHERE user_id = '.CurrentUser::getUserID().' ORDER BY id DESC';
        $addresses = $s->getAllCustom(Model::getTable('ShippingAddress'), $sql);

        $this->assign('addresses', $addresses);

        if (isset($_GET['checkout'])) {
            Session::set('shipping_address_id', $_GET['shipping_address_id']);
            $this->redirect('paypal/paypal');
        }
    }

    // taken from product admin (variant properties)
    public function getPropertiesAndOptions($p, $colours)
    {
        /*
          $v = new ProductVariant($this);
          $v->id = $_GET['id'];
          $v->load(ProductVariant::$table);
        */

        //$p = Model::load('ProductItem');
        //$p->id = $product_id;
        //$p->load();

        $c = Model::load('CategoryItem');
        $cats = $c->getAncestorIds($p->category_id, array());

        $cp = Model::load('CategoryProperty');

        array_push($cats, $p->category_id);
        $props = $cp->getPropertiesByCategory($cats);

        if (!$colours && $p->category_id != 8) {
            array_push($props, 2); // always allow colour property
        }

        //    $this->assign('product', $p);
        //$this->assign('variant', $v);

        $opts = array();
        $pv = Model::load('ProductVariantPropertyOption');
        $opts = $pv->buildUnionString($pv->getActiveOptions($p->id));

        if (sizeof($props) > 0) {
            $property = Model::load('Property');
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
