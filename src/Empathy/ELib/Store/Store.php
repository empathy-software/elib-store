<?php

namespace Empathy\ELib\Store;

use Empathy\ELib\Model,
    Empathy\ELib\User\CurrentUser,
    Empathy\MVC\Session,
    Empathy\ELib\Storage\ProductItemStatus,
    Empathy\ELib\Storage\ProductVariantStatus;




define('REQUESTS_PER_PAGE', 12);

class Store
{
    private $c;

    public function __construct($c)
    {
        $this->c = $c;
    }

    // from category controller

    public function productsView()
    {
        $ui_array = array('order_by', 'page', 'id', 'brand_id');
        Session::loadUIVars('ui_catalogue', $ui_array);
        if (!isset($_GET['page']) || $_GET['page'] == '') {
            $_GET['page'] = 1;
        }
        if (!isset($_GET['id']) || $_GET['id'] == '') {
            $_GET['id'] = 0;
        }
        if (!isset($_GET['order_by']) || $_GET['order_by'] == '') {
            $_GET['order_by'] = 'id';
        }
        if (!isset($_GET['brand_id']) || $_GET['brand_id'] == '') {
            $_GET['brand_id'] = 0;
        }

        $this->c->assign('order_by', $_GET['order_by']);
        $this->c->assign('page', $_GET['page']);
        $this->c->assign('category_id', $_GET['id']);

        $this->buildNav();

        $p = Model::load('ProductItem');
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $showCat = $_GET['id'];
        } else {
            $showCat = 0;
        }

        $sql = ' WHERE category_id = '.$_GET['id'];

        if ($_GET['brand_id'] > 0) {
            $sql .= ' AND brand_id = '.$_GET['brand_id'];
        }

        // status
        $sql .= ' AND status != '.ProductItemStatus::DELETED;

        // vendor
        $v = Model::load('Vendor');
        $vendor_id = $v->getIDByUserID(CurrentUser::getUserID());
        $sql .= ' AND vendor_id = '.$vendor_id;

        $sql .= ' ORDER BY '.$_GET['order_by'];

        $p_nav = $p->getPaginatePages(Model::getTable('ProductItem'), $sql, $_GET['page'], REQUESTS_PER_PAGE);

        $this->c->assign('p_nav', $p_nav);

        $product = $p->getAllCustomPaginate(Model::getTable('ProductItem'), $sql, $_GET['page'], REQUESTS_PER_PAGE);
        foreach ($product as &$p_item) {
            $p_item['status_text'] = ProductItemStatus::getStatus($p_item['status']);
            //$p_item['min_price'] = $p->getMinPrice($p_item['id']);
            // min price is now stored in products table
        }

        $c = Model::load('CategoryItem');
        $c->id = $_GET['id'];
        $category = $c->loadIndexed($c->category_id);
        $this->c->assign("products", $product);
    }

    public function buildNav()
    {
        if (!isset($_GET['collapsed']) || !is_numeric($_GET['collapsed'])) {
            $_GET['collapsed'] = 0;
        }

        $c = Model::load('CategoryItem');
        $c->id = $_GET['id'];
        $c->load();

        $ct = new CategoriesTree($c, $_GET['collapsed'], 'storeadmin/products');

        $this->c->assign('category', $c);
        $this->c->assign('category_has_children', $c->hasChildren());

        $this->c->assign('nav', $ct->getMarkup());

        $b = Model::load('BrandItem');
        $this->c->assign('brands', $b->getBrands());
    }

    // from product controller

    public function addProductVariant()
    {
        //$this->assertID();
        $this->addProductVariantInternal($_GET['id']);
        $this->c->redirect('storeadmin/product/'.$_GET['id']);
    }

    // (new function)
    public function addProductVariantInternal($product_id)
    {
        $v = Model::load('ProductVariant');
        $v->product_id = $product_id;
        $v->price = 'DEFAULT';
        $v->status = 'DEFAULT';
        $v->insert(Model::getTable('ProductVariant'), 1, array(), 0);
    }

    public function addProduct()
    {
        $_GET['id'] = (int) $_GET['id'];
        if ($_GET['id'] > 0) {
            $c = Model::load('CategoryItem');
            $c->id = $_GET['id'];
            if (!$c->hasChildren()) {
                $p = Model::load('ProductItem');
                $p->category_id = $_GET['id'];
                $p->name = 'New Product';
                $p->description = 'No description.';
                $p->status = 'DEFAULT';

                if(defined('ELIB_MULTIPLE_VENDORS') &&
                   ELIB_MULTIPLE_VENDORS == true) {
                    $user_id = CurrentUser::getUserID();
                    $v = Model::load('Vendor');
                    $v->id = $v->getIDByUserID($user_id);                    
                    if ($v->id > 0) {
                        $v->load();

                        if ($v->verified !== null) {
                            $p->vendor_verified = 1;
                        } else {
                            $p->vendor_verified = 0;
                        }
                        $p->vendor_id = $v->id;
                    }
                }
                $p->id = $p->insert(Model::getTable('ProductItem'), 1, array(), 0);
                $this->addProductVariantInternal($p->id); // create first variant
                $this->c->redirect('storeadmin/edit_product/'.$p->id);
            }
        }
        $this->c->redirect('storeadmin/products/'.$_GET['id']);
    }

    public function viewProduct()
    {
        //$this->setTemplate('elib://admin/product.tpl');
        $p = Model::load('ProductItem');

        $p->id = $_GET['id'];
        $p->load();
        $this->c->assign('product_status', ProductItemStatus::getStatus($p->status));

        $this->c->assign("product", $p);

        $v = Model::load('ProductVariant');
        $c = Model::load('ProductColour');

        $has_colours = $c->hasColours($p->id);
        if ($has_colours) {
            $variants = $v->getAllColourVariants($p->id);
            $ids = array();
            foreach ($variants as $index => $item) {
                array_push($ids, $item['id']);
                //if($item['image'] == '' && $item['other_image'] != '')
                if ($item['other_image'] != '') { // product colour images override variant images
                    $variants[$index]['image'] = $variants[$index]['other_image'];
                }
            }

            $sql = ' WHERE product_id = '.$p->id;
            if (sizeof($ids) > 0) {
                $sql .= ' AND id NOT IN '.$v->buildUnionString($ids);
            }
            $variants = array_merge($variants, $v->getAllCustom(Model::getTable('ProductVariant'), $sql));
        } else {
            $sql = ' WHERE product_id = '.$p->id;
            $sql .= ' AND status != '.ProductVariantStatus::DELETED;
            $variants = $v->getAllCustom(Model::getTable('ProductVariant'), $sql);
        }

        $property = Model::load('Property');

        $available_variant = false;

        foreach ($variants as &$v) {
            $props = $property->loadForVariant($v['id']);
            if (sizeof($props) > 0) {
                $v['properties'] = $props;
            }
            if($this->setStatusFlags($v) == true
               && $available_variant != true)
            {
                $available_variant = true;
            }
            $v['status_text'] = ProductVariantStatus::getStatus($v['status']);
        }

        if($available_variant == true
           && $p->name != 'New Product'
           && !($p->description == 'No description.' || $p->description == '<p>No description.</p>')
           && $p->image != '')
        {
            if ($p->status != ProductItemStatus::AVAILABLE) {
                $this->c->assign('product_available_link', true);
            }
            if ($p->status != ProductItemStatus::SOLD_OUT) {
                $this->c->assign('product_sold_out_link', true);
            }
            if ($p->status != ProductItemStatus::CREATED) {
                $this->c->assign('product_unavailable_link', true);
            }
        }

        $this->c->assign('has_colours', $has_colours);
        $this->c->assign('variants', $variants);
    }

    // new stuff
    public function setStatusFlags(&$v)
    {
        $available = false;
        if (isset($v['properties']) && $v['price'] > 0) {
            if ($v['status'] != ProductVariantStatus::AVAILABLE) {
                $v['available_link'] = true;
            } else {
                $available = true;
                $v['unavailable_link'] = true;
            }
        }

        return $available;
    }

    public function setVariantAvailable()
    {
        $v = Model::load('ProductVariant');
        $v->id = $_GET['id'];
        $v->load();
        $v->status = ProductVariantStatus::AVAILABLE;
        $v->save(Model::getTable('ProductVariant'), array(), 2);
        $this->c->redirect('storeadmin/product/'.$v->product_id);
    }

    public function productAutoGetMinPrice($product_id)
    {
        $p = Model::load('ProductItem');
        $price = $p->getMinPrice($product_id);
        if ($price > 0) {
            $p->id = $product_id;
            $p->load();
            $p->min_price = $price;
            $p->save(Model::getTable('ProductItem'), array(), 2);
        }
    }

    public function productAutoHide($product_id)
    {
        $v = Model::load('ProductVariant');
        $sql = ' WHERE status = '.ProductVariantStatus::AVAILABLE
            .' AND product_id = '.$product_id;
        $variants = $v->getAllCustom(Model::getTable('ProductVariant'), $sql);
        if (sizeof($variants) < 1) {
            $p = Model::load('ProductItem');
            $p->id = $product_id;
            $p->load();
            $p->status = ProductItemStatus::CREATED;
            $p->save(Model::getTable('ProductItem'), array(), 2);
        }
    }

    public function setVariantUnavailable()
    {
        $v = Model::load('ProductVariant');
        $v->id = $_GET['id'];
        $v->load();
        $v->status = ProductVariantStatus::CREATED;
        $v->save(Model::getTable('ProductVariant'), array(), 2);

        $this->productAutoHide($v->product_id);

        $this->c->redirect('storeadmin/product/'.$v->product_id);
    }

    public function setProductAvailable()
    {
        $this->productAutoGetMinPrice($_GET['id']);

        $p = Model::load('ProductItem');
        $p->id = $_GET['id'];
        $p->load();
        $p->status = ProductItemStatus::AVAILABLE;
        $p->save(Model::getTable('ProductItem'), array(), 2);
        $this->c->redirect('storeadmin/product/'.$p->id);
    }

    public function setProductUnAvailable()
    {
        $p = Model::load('ProductItem');
        $p->id = $_GET['id'];
        $p->load();
        $p->status = ProductItemStatus::CREATED;
        $p->save(Model::getTable('ProductItem'), array(), 2);
        $this->c->redirect('storeadmin/product/'.$p->id);
    }

    public function setProductSoldOut()
    {
        $p = Model::load('ProductItem');
        $p->id = $_GET['id'];
        $p->load();
        $p->status = ProductItemStatus::SOLD_OUT;
        $p->save(Model::getTable('ProductItem'), array(), 2);
        $this->c->redirect('storeadmin/product/'.$p->id);
    }

    public function clearVariantImage()
    {
        $v = Model::load('ProductVariant');
        $v->id = $_GET['id'];
        $v->load();

        $i = new ImageUpload(null, false, array());
        if ($v->image != '') {
            $i->remove(array($v->image));
            unset($v->image);
            $v->save(Model::getTable('ProductVariant'), array(), 2);
        }
        $this->c->redirect('storeadmin/product/'.$v->product_id);
    }

    public function editProduct()
    {
        //$this->setTemplate('elib://admin/product.tpl');
        $p = Model::load('ProductItem');
        //$pr = new ProductRange($this);

        if (isset($_POST['cancel'])) {
            $this->c->redirect('storeadmin/product/'.$_POST['id']);
        } elseif (isset($_POST['submit_product'])) {
            $p->id = $_POST['id'];

            /*
              if (!isset($_POST['range'])) {
              $_POST['range'] = array();
              }
              $range = $_POST['range'];
              $pr->updateForProduct($p->id, $range);
            */

            $p->load();
            $old_product_name = $p->name;
            $p->name = $_POST['name'];
            $p->description = $_POST['description'];

            if (isset($_POST['sold_in_store']) &&
                $_POST['sold_in_store'] == 1) {
                $p->status = 1;
            } else {
                $p->status = 0;
            }

            $p->brand_id = (isset($_POST['brand_id']))? $_POST['brand_id']: NULL;

            $p->validates();
            if ($p->hasValErrors()) {
                // old_product_name along with code in admin_header
                // prevents breadcrumb from breaking on errors
                $this->c->assign('product', $p);
                $this->c->assign('old_product_name', $old_product_name);
                $this->c->assign('errors', $p->getValErrors());
            } else {
                //$p->price = $_POST['price'];
                $p->save(Model::getTable('ProductItem'), array('description'), 1);
                $this->c->redirect('storeadmin/product/'.$p->id);
            }
        } else {
            $p->id = $_GET['id'];
            $p->load();

            $p->sold_in_store = 0;
            if ($p->status > 0) {
                $p->sold_in_store = 1;
            }

            //$product_ranges = $pr->loadForProduct($p->id);

            //$r = new RangeItem($this);
            //$ranges = $r->loadAllIndexed();

            $c = Model::load('CategoryItem');
            $category = $c->loadIndexed($c->category_id);

            //$this->presenter->assign("product_ranges", $product_ranges);
            //$this->presenter->assign("ranges", $ranges);

            $this->c->assign("product", $p);
            $this->c->assign("categories", $category);

            $sold = array();
            $sold[0] = 'No';
            $sold[1] = 'Yes';
            $this->c->assign('sold_in_store', $sold);

            $b = Model::load('BrandItem');
            $brands = $b->getBrands();
            $this->c->assign('brands', $brands);
        }
    }

    public function uploadProductImage()
    {
        //$this->setTemplate('elib://admin/product.tpl');
        if (isset($_POST['cancel'])) {
            $this->c->redirect('storeadmin/product/'.$_POST['id']);
        } elseif (isset($_POST['upload'])) {
            $_GET['id'] = $_POST['id'];
        }

        $p = Model::load('ProductItem');
        $p->id = $_GET['id'];
        $p->load();

        $this->c->assign("product", $p);

        if (isset($_POST['upload'])) {
            $d = array(
                array('l_', 450, 450),
                array('tn_', 100, 100),
                array('mid_', 400, 276)
                );
            $u = new ImageUpload('products', true, $d);

            if ($u->error != '') {
                $this->c->assign("error", $u->error);
            } else {
                if ($p->image != "") {
                    $u->remove(array($p->image));
                }
                // update db
                $p->image = $u->file;
                $p->status = ProductItemStatus::CREATED;
                $p->save(Model::getTable('ProductItem'), array(), 2);

                //$this->redirect_cgi('archive.cgi?id='.$p->id);
                //$this->execScript('archive', array($p->id));
                $this->c->redirect('storeadmin/product/'.$p->id);
            }
        }
    }

    // new function (using status codes)
    public function deleteProduct()
    {
        $p = Model::load('ProductItem');
        $p->id = $_GET['id'];
        $p->load();
        $p->status = ProductItemStatus::DELETED;
        $p->save(Model::getTable('ProductItem'), array(), 2);
        $this->c->redirect('storeadmin/products');
    }

    // new function
    public function deleteVariant()
    {
        $v = Model::load('ProductVariant');
        $v->id = $_GET['id'];
        $v->load();
        $v->status = ProductVariantStatus::DELETED;
        $v->save(Model::getTable('ProductVariant'), array(), 2);

        $this->productAutoHide($v->product_id);

        $this->c->redirect('storeadmin/product/'.$v->product_id);
    }

    public function editProductVariant()
    {
        //$this->setTemplate('elib://admin/product.tpl');
        if (isset($_POST['cancel'])) {
            $v = Model::load('ProductVariant');
            $v->id = $_POST['id'];
            $v->load();
            $this->c->redirect('storeadmin/product/'.$v->product_id);
        } elseif (isset($_POST['save'])) {
            $v = Model::load('ProductVariant');
            $v->id = $_POST['id'];
            $v->load();
            $v->weight_g = (isset($_POST['weight_g']))? $_POST['weight_g']: NULL;
            $v->weight_lb = (isset($_POST['weight_lb']))? $_POST['weight_lb']: NULL;
            $v->weight_oz = (isset($_POST['weight_oz']))? $_POST['weight_oz']: NULL;
            $v->price = $_POST['price'];
            $v->validates();
            if ($v->hasValErrors()) {
                $this->c->assign('variant', $v);
                $this->c->assign('errors', $v->getValErrors());
            } else {
                $v->save(Model::getTable('ProductVariant'), array(), 1);
                $this->c->redirect('storeadmin/product/'.$v->product_id);
            }
        } else {
            //$this->assertID();
            $v = Model::load('ProductVariant');
            $v->id = $_GET['id'];
            $v->load();
            $this->c->assign('variant', $v);
        }

        $p = Model::load('ProductItem');
        $p->id = $v->product_id;
        $p->load();
        $this->c->assign('product', $p);
    }

    public function uploadVariantImage()
    {
        //$this->setTemplate('elib://admin/product.tpl');
        //$this->assertID();
        $v = Model::load('ProductVariant');
        $v->id = $_GET['id'];
        $v->load();
        $this->c->assign('variant', $v);

        $p = Model::load('ProductItem');
        $p->id = $v->product_id;
        $p->load();
        $this->c->assign("product", $p);

        if (isset($_POST['cancel'])) {
            $this->c->redirect('storeadmin/product/'.$v->product_id);
        } elseif (isset($_POST['upload'])) {
            $d = array(array('tn_', 100, 100), array('mid_', 400, 276));
            $u = new ImageUpload('products', true, $d);

            if ($u->error != '') {
                $this->c->assign('error', $u->error);
            } else {
                if ($v->image != '') {
                    $u->remove(array($v->image));
                }
                $v->image = $u->file;
                $v->save(Model::getTable('ProductVariant'), array(), 0);

                //$this->redirect_cgi('archive.cgi?id='.$p->id);
                //$this->execScript('archive', array($p->id));
                $this->c->redirect('storeadmin/product/'.$p->id);
            }
        }
    }

    public function variantProperties()
    {
        //$this->setTemplate('elib://admin/product.tpl');
        //$this->assertID();

        if (isset($_POST['cancel'])) {
            $v = Model::load('ProductVariant');
            $v->id = $_GET['id'];
            $v->load();
            $this->c->redirect('storeadmin/product/'.$v->product_id);
        } elseif (isset($_POST['save'])) {
            $p = Model::load('ProductVariantPropertyOption');
            $p->emptyByVariant($_GET['id']);
            $p->product_variant_id = $_GET['id'];
            //if(isset($_POST['property']))
            // {
            foreach ($_POST['property'] as $index => $item) {
        if ($item > 0 && is_numeric($item)) {
            $p->property_option_id = $item;
            $p->insert(Model::getTable('ProductVariantPropertyOption'), 1, array(), 0);
                }
            }
        // }
            $v = Model::load('ProductVariant');
            $v->id = $_GET['id'];
            $v->load();
            $this->c->redirect('storeadmin/product/'.$v->product_id);
        }

        $v = Model::load('ProductVariant');
        $v->id = $_GET['id'];
        $v->load();

        $p = Model::load('ProductItem');
        $p->id = $v->product_id;
        $p->load();

        $c = Model::load('CategoryItem');
        $cats = $c->getAncestorIds($p->category_id, array());

        $cp = Model::load('CategoryProperty');

        array_push($cats, $p->category_id);
        $props = $cp->getPropertiesByCategory($cats);

        //array_push($props, 2); // always allow colour property

        $this->c->assign('product', $p);
        $this->c->assign('variant', $v);

        if (sizeof($props) > 0) {
            $property = Model::load('Property');
            $properties = $property->getAllWithOptions($props);
            $this->c->assign('properties', $properties);

            $pv = Model::load('ProductVariantPropertyOption');
            $sql = ' WHERE product_variant_id = '.$_GET['id'];
            $options = $pv->getAllCustom(Model::getTable('ProductVariantPropertyOption'), $sql);
            $o = array();
            foreach ($options as $index => $value) {
                array_push($o, $value['property_option_id']);
            }
            $this->c->assign('options', $o);
        }
    }

}
