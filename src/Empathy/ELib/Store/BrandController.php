<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\Storage\BrandItem;
use Empathy\MVC\Model;

class BrandController extends AdminController
{
    public function default_event(): void
    {
        $ui_array = ['id'];
        $this->loadUIVars('ui_banner', $ui_array);
        if (!isset($_GET['id']) || $_GET['id'] === '') {
            $_GET['id'] = 0;
        }
        $this->buildNav();
    }

    public function buildNav(): void {
        $this->setTemplate('elib://admin/brand.tpl');
        $b = Model::load(BrandItem::class);
        $b->load($_GET['id']);

        $bt = new BrandsTree($b);
        $this->presenter->assign('banners', $bt->getMarkup());
        $this->presenter->assign('artist', $b);
    }

    public function add(): void {
        $b = Model::load(BrandItem::class);
        $b->name = 'New Brand';
        $id = $b->insert();
        $this->redirect('admin/brand/'.$id);
    }

    public function rename(): void {
        $this->buildNav();
        if (isset($_POST['save'])) {
            $b = Model::load(BrandItem::class);
            $b->load($_POST['id']);
            $b->name = $_POST['artist_alias'];
            $b->validates();
            if ($b->hasValErrors()) {
                $this->presenter->assign('brand', $b);
                $this->presenter->assign('errors', $b->getValErrors());
            } else {
                $b->save();
                $this->redirect('admin/brand/' . $b->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/brand/' . $_POST['id']);
        } else {
            $b = Model::load(BrandItem::class);
            $b->load($_GET['id']);
            $this->presenter->assign('brand', $b);
        }
    }

    public function assertID(): void {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_GET['id'] = 0;
        }
    }

    public function delete(): void {
        $this->assertID();
        $b = Model::load(BrandItem::class);
        $b->load($_GET['id']);
        $b->delete();
        $this->redirect('admin/brand/');
    }

    public function edit_bio(): void {
        if (isset($_POST['save'])) {
            $b = Model::load(BrandItem::class);
            $b->load($_POST['id']);
            $b->about = $_POST['bio'];
            $b->validates();
            if ($b->hasValErrors()) {
                $this->presenter->assign('brand', $b);
                $this->presenter->assign('errors', $b->getValErrors());
            } else {
                $b->save(['bio']);
                $this->redirect('admin/brand/'.$_GET['id']);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/brand/'.$_GET['id']);
        }

        $this->buildNav();
        $b = Model::load(BrandItem::class);
        $b->load($_GET['id']);
        $this->presenter->assign('brand', $b);
    }

}
