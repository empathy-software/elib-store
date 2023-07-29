<?php

namespace Empathy\ELib\Store;

use Empathy\ELib\Model;


class ProductController extends AdminController
{
    public function assertID()
    {
        if (!isset($_GET['id'] ) || !is_numeric($_GET['id'])) {
            $this->redirect('admin/category');
        }
    }

    public function edit()
    {
        $this->setTemplate('elib://admin/product.tpl');
        $p = Model::load('ProductItem');
        //$pr = new ProductRange($this);

        if (isset($_POST['submit_product'])) {
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

            if ($_POST['sold_in_store'] == 1) {
                $p->status = 1;
            } else {
                $p->status = 0;
            }

            $p->brand_id = $_POST['brand_id'];

            $p->validates();
            if ($p->hasValErrors()) {
                // old_product_name along with code in admin_header
                // prevents breadcrumb from breaking on errors
                $this->presenter->assign('product', $p);
                $this->presenter->assign('old_product_name', $old_product_name);
                $this->presenter->assign('errors', $p->getValErrors());
            } else {
                //$p->price = $_POST['price'];
                $p->save(Model::getTable('ProductItem'), array('description'), 1);
                $this->redirect('admin/product/' . $p->id);
            }
        } else if (isset($_POST['cancel'])) {
            $this->redirect('admin/product/' . $_GET['id']);
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

            $this->presenter->assign("product", $p);
            $this->presenter->assign("categories", $category);

            $sold = array();
            $sold[0] = 'No';
            $sold[1] = 'Yes';
            $this->presenter->assign('sold_in_store', $sold);

            $b = Model::load('BrandItem');
            $brands = $b->getBrands();
            $this->presenter->assign('brands', $brands);

        }
    }

    public function default_event()
    {
        $this->setTemplate('elib://admin/product.tpl');
        $p = Model::load('ProductItem');

        $p->id = $_GET['id'];
        $p->load();

        $this->presenter->assign("product", $p);

        if (is_numeric($p->brand_id)) {
            $b = Model::load('BrandItem');
            $b->id = $p->brand_id;
            $b->load();
            $this->presenter->assign('brand', $b->name);
        }

        $v = Model::load('ProductVariant');
        $c = Model::load('ProductColour');

        $has_colours = $c->hasColours($p->id);
        if ($has_colours) {
            $variants = $v->getAllColourVariants($p->id);
            $ids = array();
            foreach ($variants as $index => $item) {
                array_push($ids, $item['id']);
                //if($item['image'] == '' && $item['other_image'] != '')
                // product colour images override variant images
                if ($item['other_image'] != '') {
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
            $variants = $v->getAllCustom(Model::getTable('ProductVariant'), $sql);
        }

        $property = Model::load('Property');

        foreach ($variants as $index => $item) {
            $props = $property->loadForVariant($item['id']);
            $variants[$index]['properties'] = $props;
        }

        $this->presenter->assign('has_colours', $has_colours);

        $this->presenter->assign('variants', $variants);
    }

    public function upload_image()
    {
        $this->setTemplate('elib://admin/product.tpl');
        if (isset($_POST['upload'])) {
            $_GET['id'] = $_POST['id'];
        }

        $p = Model::load('ProductItem');
        $p->id = $_GET['id'];
        $p->load();

        $this->presenter->assign("product", $p);

        if (isset($_POST['upload'])) {
            $d = array(array('tn_', 100, 100), array('mid_', 400, 276));
            $u = new ImageUpload('products', true, $d);

            if ($u->error != '') {
                $this->presenter->assign("error", $u->error);
            } else {
                if ($p->image != "") {
                    $u->remove(array($p->image));
                }
                // update db
                $p->image = $u->file;
                $p->save(Model::getTable('ProductItem'), array(), 2);
                $this->redirect('admin/product/'.$p->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/product/' . $_POST['id']);
        }
    }

    public function resize_images()
    {
        $this->setTemplate('elib://admin/product.tpl');
        if (isset($_POST['submit'])) {
            set_time_limit(300);
            if(isset($_POST['tn_width']) && is_numeric($_POST['tn_width'])
               && isset($_POST['tn_height']) && is_numeric($_POST['tn_height'])
               && isset($_POST['mid_width']) && is_numeric($_POST['mid_height']))
            {
                $p = Model::load('ProductItem');
                $images = $p->getAllImages();

                $d = array(array('tn_', $_POST['tn_width'], $_POST['tn_height']),
                           array('mid_', $_POST['mid_width'], $_POST['mid_height']));
                $u = new ImageUpload('', false, $d);
                $u->resize($images);
            }
        }
    }

    public function delete()
    {
        $p = Model::load('ProductItem');
        $p->id = $_GET['id'];
        $p->load();
        if (!$p->hasVariants()) {
            $images_removed = false;
            if ($p->image != '') {
                $u = new ImageUpload('products', false, array());
                if ($u->remove(array($p->image))) {
                    $images_removed = true;
                }
            }
            if ($p->image == '' || $images_removed) {
                $p->delete();
                $this->redirect('admin/category/'.$p->category_id);
            }
        } else {
            $this->redirect('admin/product/'.$p->id);
        }
    }

    public function delete_variant()
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_GET['id'] = 0;
        }
        $v = Model::load('ProductVariant');
        $v->id = $_GET['id'];
        $v->load();
        $images_removed = false;
        if ($v->image != '') {
            $i = new ImageUpload('products', false, array());
            if ($i->remove(array($v->image))) {
                $images_removed = true;
            }
        }
        if ($v->image == '' || $images_removed) {
            $o = Model::load('ProductVariantPropertyOption');
            $o->emptyByVariant($v->id);
            $v->delete();
        }
        $this->redirect('admin/product/'.$v->product_id);
    }

    public function add_variant()
    {
        $this->assertID();
        $v = Model::load('ProductVariant');
        $v->product_id = $_GET['id'];
        $v->weight_g = 0;
        $v->weight_lb = 0;
        $v->weight_oz = 0;
        $v->price = 'DEFAULT';
        $v->status = 'DEFAULT';
        $v->insert(Model::getTable('ProductVariant'), 1, array(), 0);
        $this->redirect('admin/product/'.$_GET['id']);
    }

    public function add()
    {
        $p = Model::load('ProductItem');
        $p->category_id = $_GET['id'];
        $p->brand_id = 0;
        $p->name = 'New Product';
        $p->description = 'No description.';
        $p->status = 'DEFAULT';
        $p->vendor_verified = 'DEFAULT';
        $p->insert(Model::getTable('ProductItem'), 1, array(), 0);
        $this->redirect('admin/category/'.$_GET['id']);
    }

    public function edit_variant()
    {
        $this->assertID();
        $this->setTemplate('elib://admin/product.tpl');
        $v = Model::load('ProductVariant');
        $v->id = $_GET['id'];
        $v->load();
        $this->assign('variant', $v);
        $p = Model::load('ProductItem');
        $p->id = $v->product_id;
        $p->load();
        $this->presenter->assign('product', $p);
        // get variant data
        $v_array = json_decode(json_encode($v), JSON_OBJECT_AS_ARRAY);
        $props = Model::load('Property');
        $v_array['properties'] = $props->loadForVariant($v_array['id']);
        $this->assign('v', $v_array);
        
        if (isset($_POST['save'])) {
            $v = Model::load('ProductVariant');
            $v->id = $_POST['id'];
            $v->load();
            $v->weight_g = $_POST['weight_g'];
            $v->weight_lb = $_POST['weight_lb'];
            $v->weight_oz = $_POST['weight_oz'];
            $v->price = $_POST['price'];
            $v->validates();
            if ($v->hasValErrors()) {
                $this->presenter->assign('variant', $v);
                $this->presenter->assign('errors', $v->getValErrors());
            } else {
                $v->save(Model::getTable('ProductVariant'), array(), 1);
                $this->redirect('admin/product/' . $v->product_id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/product/' . $v->product_id);
        }
    }

    public function upload_variant_image()
    {
        $this->setTemplate('elib://admin/product.tpl');
        $this->assertID();
        $v = Model::load('ProductVariant');
        $v->id = $_GET['id'];
        $v->load();

        $this->presenter->assign('variant', $v);

        // get variant data
        $v_array = json_decode(json_encode($v), JSON_OBJECT_AS_ARRAY);
        $props = Model::load('Property');
        $v_array['properties'] = $props->loadForVariant($v_array['id']);
        $this->assign('v', $v_array);

        $p = Model::load('ProductItem');
        $p->id = $v->product_id;
        $p->load();
        $this->presenter->assign("product", $p);
        

        if (isset($_POST['upload'])) {
            $d = array(array('tn_', 100, 100), array('mid_', 400, 276));
            $u = new ImageUpload('products', true, $d);

            if ($u->error != '') {
                $this->presenter->assign('error', $u->error);
            } else {
                if ($v->image != '') {
                    $u->remove(array($v->image));
                }
                $v->image = $u->file;
                $v->save(Model::getTable('ProductVariant'), array(), 0);
                $this->redirect('admin/product/'.$p->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/product/' . $p->id);
        }
    }

    public function variant_properties()
    {
        $this->setTemplate('elib://admin/product.tpl');
        $this->assertID();

        if (isset($_POST['save'])) {
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
            $this->redirect('admin/product/'.$v->product_id);
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

        $this->presenter->assign('product', $p);
        $this->presenter->assign('variant', $v);

        if (sizeof($props) > 0) {
            $property = Model::load('Property');
            $properties = $property->getAllWithOptions($props);
            $this->presenter->assign('properties', $properties);

            $pv = Model::load('ProductVariantPropertyOption');
            $sql = ' WHERE product_variant_id = '.$_GET['id'];
            $options = $pv->getAllCustom(Model::getTable('ProductVariantPropertyOption'), $sql);
            $o = array();
            foreach ($options as $index => $value) {
                array_push($o, $value['property_option_id']);
            }
            $this->presenter->assign('options', $o);
        }
    }

    public function edit_colours()
    {
        $this->setTemplate('elib://admin/product.tpl');
        $p = Model::load('ProductItem');
        $p->id = $_GET['id'];
        $p->load();

        $c = Model::load('ProductColour');
        $sql = ' WHERE t1.property_option_id = t2.id AND t1.product_id = '.$p->id;
        $select = 't1.id AS id,t1.image,t2.option_val';
        $colours = $c->getAllCustomPaginateSimpleJoin($select, Model::getTable('ProductColour'), Model::getTable('PropertyOption'), $sql, 1, 100);

        $this->presenter->assign('colours', $colours);
        $this->presenter->assign('product', $p);
    }

    public function add_colour()
    {
        $this->setTemplate('elib://admin/product.tpl');
        $p = Model::load('ProductItem');
        $p->id = $_GET['id'];
        $p->load();
        $this->presenter->assign('product', $p);

        $o = Model::load('PropertyOption');
        $colours = $o->getColoursIndexed(2);
        $this->presenter->assign('colours', $colours);

        if (isset($_POST['submit_colour'])) {
            $d = array(array('tn_', 100, 100), array('mid_', 400, 276));
            $u = new ImageUpload('', true, $d);

            if ($u->error != '') {
                $this->presenter->assign("error", $u->error);
            } else {
                // update db
                $c = Model::load('ProductColour');
                $c->product_id = $_POST['id'];
                $c->property_option_id = $_POST['colour'];
                $c->image = $u->file;
                $c->insert(Model::getTable('ProductColour'), true, array(), 2);

                $this->redirect('admin/product/edit_colours/'.$_POST['id']);
            }

        }
    }

    public function delete_colour()
    {
        $p = Model::load('ProductColour');
        $p->id = $_GET['id'];
        $p->load();

        $images_removed = false;
        if ($p->image != '') {
            $u = new ImageUpload('', false, array());
            if ($u->remove(array($p->image))) {
                $images_removed = true;
            }
        }
        if ($p->image == '' || $images_removed) {
            $p->delete();
        }
        $this->redirect('admin/product/edit_colours/'.$p->product_id);
    }

    public function edit_colour()
    {
        $this->setTemplate('elib://admin/product.tpl');
        if (isset($_POST['save_colour'])) {
            $c = Model::load('ProductColour');
            $c->id = $_POST['id'];
            $c->load();
            $c->property_option_id = $_POST['colour'];

            if ($_FILES['file']['name'] != '') {
                $images_removed = false;
                $u = new ImageUpload('', false, array());
                if ($u->remove(array($c->image))) {
                    $images_removed = true;
                }
                if ($c->image == '' || $images_removed) {
                    $d = array(array('tn_', 100, 100), array('mid_', 400, 276));
                    $u = new ImageUpload('', true, $d);

                    if ($u->error != '') {
                        $this->presenter->assign("error", $u->error);
                    } else {
                        $c->image = $u->file;
                    }
                }
            }

            $c->save(Model::getTable('ProductColour'), array(), 0);
            $this->redirect('admin/product/edit_colours/'.$c->product_id);
        }

        $c = Model::load('ProductColour');
        $c->id = $_GET['id'];
        $c->load();

        $p = Model::load('ProductItem');
        $p->id = $c->product_id;
        $p->load();

        $this->presenter->assign('product', $p);
        $this->presenter->assign('product_colour', $c);

        $o = Model::load('PropertyOption');
        $colours = $o->getColoursIndexed(2);

        $this->presenter->assign('colours', $colours);

        $this->presenter->assign('colour', $colours[$c->property_option_id]);
    }

    public function variants_wizard()
    {
        $this->setTemplate('elib://admin/product.tpl');
        if (isset($_POST['submit'])) {
            $sets = array();
            foreach ($_POST['property'] as $index => $value) {
                array_push($sets, $value);
            }

            $g = new CombGen($sets);
            $results = $g->getResults();

            $v = Model::load('ProductVariant');
            $v->product_id = $_POST['product_id'];
            $v->weight_g = $_POST['weight_g'];
            $v->weight_lb = $_POST['weight_lb'];
            $v->weight_oz = $_POST['weight_oz'];
            $v->price = $_POST['price'];
            $v->status = 1;

            $v->validates();
            if ($v->hasValErrors()) {
                //die('errors');
            } else {
                foreach ($results as $item) {
                    $v->id = $v->insert(Model::getTable('ProductVariant'), 1, array(), 0);
                    $options = explode('-', $item);
                    foreach ($options as $o) {
                        $p = Model::load('ProductVariantPropertyOption');
                        $p->product_variant_id = $v->id;
                        $p->property_option_id = $o;
                        $p->insert(Model::getTable('ProductVariantPropertyOption'), 1, array(), 0);
                    }
                }
            }
            $this->redirect('admin/product/'.$v->product_id);
        }

        $p = Model::load('ProductItem');
        $p->id = $_GET['id'];
        $p->load();
        $this->presenter->assign('product', $p);

        $c = Model::load('CategoryItem');
        $cats = $c->getAncestorIds($p->category_id, array());
        $cp = Model::load('CategoryProperty');
        array_push($cats, $p->category_id);
        $props = $cp->getPropertiesByCategory($cats);
        //array_push($props, 2); // always allow colour property

        if (sizeof($props) > 0) {
            $property = Model::load('Property');
            $properties = $property->getAllWithOptions($props);
            $this->presenter->assign('properties', $properties);

            /*
              $pv = new ProductVariantPropertyOption($this);
              $sql = ' WHERE product_variant_id = '.$_GET['id'];
              $options = $pv->getAllCustom(ProductVariantPropertyOption::$table, $sql);
              $o = array();
              foreach ($options as $index => $value) {
              array_push($o, $value['property_option_id']);
              }
              $this->presenter->assign('options', $o);
            */
        }

        $v = Model::load('ProductVariant');
        $v->weight_g = 0;
        $v->weight_lb = '0.00';
        $v->weight_oz = 0;
        $v->price = '0.00';
        $this->presenter->assign('variant', $v);

        // get colours
        $c = Model::load('ProductColour');
        $colours = $c->getColourOptionIDs($p->id);
        $this->presenter->assign('colours', $colours);

    }

    /*
      public function attributes()
      {
      if (!isset($_GET['id'])) {
      $_GET['id'] = $_POST['product_id'];
      }

      $p = new ProductItem($this);
      $p->id = $_GET['id'];
      $p->load(ProductItem::$table);

      $a = new Attribute($this);
      $pa = new ProductAttribute($this);

      $s = new StockItem($this);
      $stock_exists = $s->stockExists($p->id);

      if (isset($_POST['save_attr']) && !$stock_exists) {
      if (!isset($_POST['attribute'])) {
      $_POST['attribute'] = array();
      }

      $attribute = $_POST['attribute'];

      $product_id = $p->id;

      $pa->updateForProduct($product_id, $attribute);
      $this->redirect("admin/products");
      } else {
      $attr = $a->loadIndexed();
      $selected_attr = $pa->loadForProduct($p->id);

      $this->presenter->assign("stock_exists", $stock_exists);
      $this->presenter->assign("selected_attr", $selected_attr);
      $this->presenter->assign("attributes", $attr);
      $this->presenter->assign("product", $p);
      }
      }

      // image related
      public function unlinkImage($file)
      {
      unlink(DOC_ROOT.PUBLIC_DIR."/img/uploads/$file");
      }

      public function reset_image()
      {
      $data = new DataItem();

      if (isset($_POST['reset']) || isset($_POST['cancel'])) {
      $_GET['id'] = $_POST['id'];
      $data->id = $_GET['id'];
      $data->getItem();

      if (isset($_POST['reset'])) {
      $this->unlinkImage($data->image);

      $data->resetImage();
      }
      $this->redirect("admin/?section=sections&id=".$data->section_id);
      }

      $data->getItem();
      $this->setTemplate("data_item.tpl");
      $this->presenter->assign("operation", "Reset Image");
      $this->presenter->assign("data", $data);
      $this->setNavigation($data->section_id, $data->heading);
      }
    */

}
