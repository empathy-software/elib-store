<?php

namespace Empathy\ELib\Store;

use Empathy\ELib\Model;

class BrandController extends AdminController
{

    public function default_event()
    {
        $ui_array = array('id');
        $this->loadUIVars('ui_banner', $ui_array);
        if (!isset($_GET['id']) || $_GET['id'] == '') {
            $_GET['id'] = 0;
        }
        $this->buildNav();
    }

    public function buildNav()
    {
        $this->setTemplate('elib://admin/brand.tpl');
        $b = Model::load('BrandItem');
        $b->id = $_GET['id'];
        $b->load();

        $bt = new BrandsTree($b);
        $this->presenter->assign('banners', $bt->getMarkup());
        $this->presenter->assign('artist', $b);
    }

    public function add()
    {
        $b = Model::load('BrandItem');
        $b->name = 'New Brand';
        $id = $b->insert(Model::getTable('BrandItem'), 1, array(), 1);
        $this->redirect('admin/brand/'.$id);
    }

    public function rename()
    {
        $this->buildNav();
        if (isset($_POST['save'])) {
            $b = Model::load('BrandItem');
            $b->id = $_POST['id'];
            $b->load();
            $b->name = $_POST['artist_alias'];
            $b->validates();
            if ($b->hasValErrors()) {
                $this->presenter->assign('brand', $b);
                $this->presenter->assign('errors', $b->getValErrors());
            } else {
                $b->save(Model::getTable('BrandItem'), array(), 1);
                $this->redirect('admin/brand/' . $b->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/brand/' . $_POST['id']);
        } else {
            $b = Model::load('BrandItem');
            $b->id = $_GET['id'];
            $b->load();
            $this->presenter->assign('brand', $b);
        }
    }

    public function assertID()
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_GET['id'] = 0;
        }
    }

    public function delete()
    {
        $this->assertID();
        $b = Model::load('BrandItem');
        $b->id = $_GET['id'];
        $b->load();
        $b->delete();
        $this->redirect('admin/brand/');
    }

    public function edit_bio()
    {
        if (isset($_POST['save'])) {
            $b = Model::load('BrandItem');
            $b->id = $_POST['id'];
            $b->load();
            $b->about = $_POST['bio'];
            $b->validates();
            if ($b->hasValErrors()) {
                $this->presenter->assign('brand', $b);
                $this->presenter->assign('errors', $b->getValErrors());
            } else {
                $b->save(Model::getTable('BrandItem'), array('bio'), 1);
                $this->redirect('admin/brand/'.$_GET['id']);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/brand/'.$_GET['id']);
        }

        $this->buildNav();
        $b = Model::load('BrandItem');
        $b->id = $_GET['id'];
        $b->load();
        $this->presenter->assign('brand', $b);
    }

}
