<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\Storage\PromoItem;
use Empathy\MVC\Model;

class PromoController extends AdminController
{
    public function assertID(): void
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $this->redirect('admin/category');
        }
    }

    public function edit(): void
    {
        $this->setTemplate('elib://admin/promo.tpl');
        $p = Model::load(PromoItem::class);
        //$pr = new ProductRange($this);

        if (isset($_POST['save'])) {
            /*
              if (!isset($_POST['range'])) {
              $_POST['range'] = array();
              }
              $range = $_POST['range'];
              $pr->updateForProduct($p->id, $range);
            */

            $p->load((int) $_POST['id']);
            $old_promo = $p->name;
            $p->name = $_POST['name'];
            $p->url = $_POST['url'];

            $p->validates();
            if ($p->hasValErrors()) {
                // old_product_name along with code in admin_header
                // prevents breadcrumb from breaking on errors
                $this->assign('promo', $p);
                $this->assign('old_promo', $old_promo);
                $this->assign('errors', $p->getValErrors());
            } else {
                //$p->price = $_POST['price'];
                $p->save();
                $this->redirect('admin/promo/'.$p->id);
            }
        } else {
            $p->load((int) $_GET['id']);
            //$product_ranges = $pr->loadForProduct($p->id);

            //$r = new RangeItem($this);
            //$ranges = $r->loadAllIndexed();
            $this->assign('promo', $p);
        }
    }

    #[\Override]
    public function default_event(): void
    {
        $this->setTemplate('elib://admin/promo.tpl');
        $p = Model::load(PromoItem::class);
        $p->load((int) $_GET['id']);

        $this->assign('promo', $p);
    }

    public function upload_image(): void
    {
        $this->setTemplate('elib://admin/promo.tpl');
        if (isset($_POST['upload'])) {
            $_GET['id'] = $_POST['id'];
        }

        $p = Model::load(PromoItem::class);
        $p->load((int) $_GET['id']);

        $this->assign('promo', $p);

        if (isset($_POST['upload'])) {
            $d = [['mid_', 770, 300]];
            $u = new ImageUpload('', true, $d);

            if ($u->error !== '') {
                $this->assign('error', $u->error);
            } else {
                if ($p->image !== '') {
                    $u->remove([$p->image]);
                }
                // update db
                $p->image = $u->file;
                $p->save();

                //$this->redirect_cgi('archive.cgi?id='.$p->id);
                //$this->execScript('archive', array($p->id));
                $this->redirect('admin/promo/'.$p->id);
            }
        }
    }

    public function delete(): void
    {
        $p = Model::load(PromoItem::class);
        $p->load((int) $_GET['id']);

        $images_removed = false;
        if ($p->image !== '') {
            $u = new ImageUpload('', false, []);
            if ($u->remove([$p->image])) {
                $images_removed = true;
            }
        }
        if ($p->image === '' || $images_removed) {
            $p->delete();
            $this->redirect('admin/promo_category/'.$p->category_id);
        }
    }

}
