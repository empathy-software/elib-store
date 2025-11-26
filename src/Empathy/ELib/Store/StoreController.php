<?php

namespace Empathy\ELib\Store;

use Empathy\MVC\Model;
use Empathy\ELib\Country\Country;
use Empathy\ELib\Storage\CategoryItem;
use Empathy\ELib\Storage\BrandItem;
use Empathy\ELib\Storage\ProductColour;
use Empathy\ELib\Storage\ShippingAddress;
use Empathy\MVC\DI;

class StoreController extends StoreControllerLite
{

    public function default_event()
    {
        $this->setTemplate('elib://store_category.tpl');
    }

    public function defaultLayout()
    {
        $ui_array = array('page');
        $this->loadUIVars('ui_cats', $ui_array);
        if (!isset($_GET['page'])) {
            $_GET['page'] = 1;
        }

        $category_id = 0;

        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $category_id = $_GET['id'];
        }

        if (isset($_GET['category_name'])) {
            $cl = Model::load(CategoryItem::class);
            $category_id = $cl->loadIDByName($_GET['category_name']);
        }

        $_GET['category_id'] = $category_id;
        $_SESSION['last_cat'] = $category_id;

        $_GET['product_id'] = 0;
        $_GET['option_id'] = 0;

        $p = Model::load('ProductItem');
        $c = Model::load('CategoryItem');
        $v = Model::load('ProductVariant');
        $o = Model::load('ProductVariantPropertyOption');

        $c->id = $_GET['category_id'];
        $p->id = 0;
        $v->id = 0;
        $o->id = 0;

        $l = new ProductsLayout($c, $p, $v, $o, $this);

        $this->presenter->assign('breadcrumb', $l->getBreadCrumb());
        $this->presenter->assign('buttons', $l->getButtons());
        $this->presenter->assign('p_nav', $l->getPNav());

        $this->presenter->assign('category_id', $_GET['category_id']);
        $this->presenter->assign('product_id', $_GET['product_id']);
        $this->presenter->assign('option_id', $_GET['option_id']);

        $this->getPromos($_GET['category_id']);

        // seo
        if ($category_id > 0) {
            $this->presenter->assign('custom_title', $c->name.' at Brighton BMX Co');
            $this->presenter->assign('category_name', $c->name);
        }
    }

    public function product()
    {
        $this->setTemplate('store_product.tpl');
        $product_id = 0;

        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $product_id = $_GET['id'];
        }

        if (isset($_GET['product_name'])) {
            $name_arr = explode('-', $_GET['product_name']);
            $product_id = $name_arr[sizeof($name_arr)-1];
        }

        $_GET['product_id'] = $product_id;

        if ($this->isXMLHttpRequest()) {
            header('Content-type: application/json');
            $c = Model::load('ProductColour');
            $c->id = $_GET['colour_id'];
            $c->load();
            $item = array();
            $item['image'] = $c->image;
            $item['option_id'] = $c->property_option_id;
            echo json_encode($item);
            exit();
        } else {
            // default event from store.php

            $p = Model::load('ProductItem');
            $c = Model::load('CategoryItem');
            $v = Model::load('ProductVariant');
            $o = Model::load('ProductVariantPropertyOption');

            $c->id = 0;
            $p->id = $_GET['product_id'];
            $v->id = 0;
            $o->id = 0;

            if (isset($_POST['add'])) {
                $options = array();
                if (isset($_POST['property'])) {
                    foreach ($_POST['property'] as $option) {
                        array_push($options, $option);
                    }
                }

                if (sizeof($options) > 0) {
                    $variant_id = $v->findVariant($options, $p->id);
                } else {
                    $sql = ' WHERE product_id = ? LIMIT 0, 1';
                    $variant = $v->getAllCustom($sql, [$p->id]);
                    if (sizeof($variant) > 0) {
                        $variant_id = $variant[0]['id'];
                    }
                }

                if (is_numeric($variant_id) && $variant_id > 0) {
                    $sc = new ShoppingCart();
                    $sc->add($variant_id, 1);
                    $this->redirect('store/cart');
                }
            }

            $l = new ProductsLayout($c, $p, $v, $o, $this);

            $this->assign('breadcrumb', $l->getBreadCrumb());
            $this->assign('buttons', $l->getButtons());

            $this->assign('p_nav', $l->getPNav());

            if ($_GET['product_id'] != 0) {
                $c = Model::load(ProductColour::class);
                $colours = $c->getColoursIndexed($_GET['product_id']);
                if (sizeof($colours) > 0) {
                    $this->assign('colours', $colours);
                }

                if(defined('ELIB_USE_PRODUCT_BRANDS') &&
                   ELIB_USE_PRODUCT_BRANDS == true)
                {
                    $b = Model::load(BrandItem::class);
                    $b->id = $p->brand_id;
                    $b->load($b->id);
                    $this->assign('brand', $b->name);
                }
                $this->assign('product_view', 1);
                //$variant_count = $this->assignVariantsTableData($p, $v);
                //$this->getPropertiesAndOptions($p->id, (sizeof($colours) > 0));
                $this->getPropertiesAndOptions($p, (sizeof($colours) > 0));
            }

            $_GET['category_id'] = -1;

            $this->assign('category_id', $_GET['category_id']);
            $this->assign('product_id', $_GET['product_id']);
            $this->assign('option_id', 0);

            if ($_GET['product_id'] != 0) {
                $p = $l->getProduct();
                if (sizeof($colours) > 0) {
                    $p->image = $c->getFirstColourImage($_GET['product_id']);
                }

                $this->assign('product', $p);

                $qty = array();
                $i = 0;
                for ($i; $i < 31; $i++) {
                    $qty[$i] = $i;
                }

                $this->assign('qty', $qty);
            }
        }

        // seo
        $c = Model::load(CategoryItem::class);
        $c->id = $p->category_id;
        $c->load($c->id);
        $this->assign('price', $p->getPrice());

        $custom_title = '';
        $custom_keywords = '';
        $custom_description = '';
        if (isset($b)) {
            $custom_title = $b->name.' ';
            $custom_keywords = $b->name.' ';
            $custom_description = $b->name.' ';
        }
        $custom_title .= $p->name.' - '.$c->name.' at Brighton BMX Co';
        $custom_keywords .= $p->name.' '.$c->name;
        $custom_description .= $p->name.' in '.$c->name;

        $this->assign('custom_title', $custom_title);
        $this->assign('custom_keywords', strtolower($custom_keywords));
        $this->assign('custom_description', $custom_description.' - '.strip_tags($p->description));
    }

    // copied across from store.php
    public function getPromos($category_id)
    {
        $p = Model::load('PromoItem');

        $c = Model::load('CategoryItem');
        $ids = array();
        $c->buildDescendantIDs($category_id, $ids);
        $cat_string = $c->buildUnionString($ids);

        // remove root category unless in root
        $cat_string[0] = str_replace('(0,', '(', $cat_string[0]);
        shift($cat_string[1]);

        $sql = ' WHERE category_id IN '.$cat_string[0]
            .' AND image IS NOT NULL'
            .' AND url IS NOT NULL'
            .' ORDER BY id DESC';
        $promos = $p->getAllCustom($sql, $cat_string[1]);
        shuffle($promos);
        $this->presenter->assign('promos', $promos);
    }

    public function accepting_paypal()
    {
        $this->setTemplate('accepting_paypal.tpl');
    }

    public function edit_address()
    {
        $this->setTemplate('address.tpl');
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            if (isset($_POST['save'])) {
                $s = Model::load(ShippingAddress::class);
                $s->load($_GET['id']);
                $s->first_name = $_POST['first_name'];
                $s->last_name = $_POST['last_name'];
                $s->address1 = $_POST['address1'];
                $s->address2  = $_POST['address2'];
                $s->city = $_POST['city'];
                $s->state = $_POST['state'];
                $s->zip = $_POST['zip'];
                $s->country = $_POST['country'];
                $s->validates();
                if ($s->hasValErrors()) {
                    $this->presenter->assign('address', $s);
                    $this->presenter->assign('sc', $s->country);

                    $this->presenter->assign('errors', $s->getValErrors());

                } else {
                    $s->save();
                    $this->redirect('store/checkout');
                }
            } else {
                $s = Model::load(ShippingAddress::class);
                $s->id = $_GET['id'];
                $s->load();
                $this->presenter->assign('address', $s);
                $this->presenter->assign('sc', $s->country);
            }

            $countries = Country::build();
            $this->presenter->assign('countries', $countries);
        }
    }

    public function add_address()
    {
        $this->setTemplate('address.tpl');
        $countries = Country::build();
        $this->presenter->assign('countries', $countries);
        $this->presenter->assign('sc', 'GB');

        if (isset($_POST['save'])) {
            $s = Model::load(ShippingAddress::class);
            $s->user_id = DI::getContainer()->get('CurrentUser')->getUserID();
            $s->first_name = $_POST['first_name'];
            $s->last_name = $_POST['last_name'];
            $s->address1 = $_POST['address1'];
            $s->address2  = $_POST['address2'];
            $s->city = $_POST['city'];
            $s->state = $_POST['state'];
            $s->zip = $_POST['zip'];
            $s->country = $_POST['country'];
            $s->validates();
            if ($s->hasValErrors()) {
                $this->presenter->assign('address', $s);
                $this->presenter->assign('sc', $s->country);
                $this->presenter->assign('errors', $s->getValErrors());
            } else {
                $s->insert();
                $this->redirect('store/checkout');
            }
        }
    }

    public function assignVariantsTableData($p, $v)
    {
        $variants = $v->getAllForProduct($p->id, $p->name);

        $total_weight_g = 0;
        $total_weight_lb = 0;
        foreach ($variants as $item) {
            if (isset($item['weight_g'])) {
                $total_weight_g += $item['weight_g'];

            }
            if (isset($item['weight_lb'])) {
                $total_weight_lb += $item['weight_lb'];
            }
        }

        //    print_r($variants);
        $fixed_properties = array('id', 'weight_g', 'weight_lb', 'name');
        $dynamic_properties = array();
        foreach ($variants as $index => $value) {
            foreach ($value as $index2 => $value2) {
                if (!in_array($index2, $fixed_properties) && !in_array($index2, $dynamic_properties)) {
                    array_push($dynamic_properties, $index2);
                }
            }
        }
        $this->presenter->assign('variants', $variants);
        $this->presenter->assign('dynamic_properties', $dynamic_properties);
        $this->presenter->assign('total_weight_g', $total_weight_g);
        $this->presenter->assign('total_weight_lb', $total_weight_lb);

        return sizeof($variants);
    }

}
