<?php

namespace Empathy\ELib\Store;

use Empathy\MVC\Model;

class ArtistController extends AdminController
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

    public function toggle_active()
    {
        $a = Model::load('ArtistItem');
        $a->id = $_GET['id'];
        $a->load();
        $a->active = ($a->active) ? 0 : 1;
        $a->save(Model::getTable('ArtistItem'), array(), 0);
        $this->redirect('admin/artist/'.$a->id);
    }

    public function buildNav()
    {
        $this->setTemplate('elib://admin/artist.tpl');
        //$this->assertID();

        $a = Model::load('ArtistItem');
        $a->id = $_GET['id'];
        $a->load();

        $at = new ArtistsTree($a);
        $this->presenter->assign('banners', $at->getMarkup());
        $this->presenter->assign('artist', $a);
    }

    public function add()
    {
        if (isset($_POST['save'])) {
            $a = Model::load('ArtistItem');
            //$a->artist_alias = $_POST['artist_alias'];
            $a->forename = $_POST['forename'];
            $a->surname = $_POST['surname'];
            $a->validates();
            if ($a->hasValErrors()) {
                $this->presenter->assign('artist', $a);
                $this->presenter->assign('errors', $a->getValErrors());
            } else {
                $a->artist_alias = '';
                $a->active = 0;
                $a->insert(Model::getTable('ArtistItem'), true, array(), 1);
                $this->redirect('admin/artist/'.$a->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/artist');
        }

        $this->setTemplate('elib://admin/add_artist.tpl');
    }

    public function rename()
    {
        $this->buildNav();
        if (isset($_POST['save'])) {
            $a = Model::load('ArtistItem');
            $a->id = $_POST['id'];
            $a->load();
            //$a->artist_alias = $_POST['artist_alias'];
            $a->forename = $_POST['forename'];
            $a->surname = $_POST['surname'];
            $a->validates();
            if ($a->hasValErrors()) {
                $this->presenter->assign('artist', $a);
                $this->presenter->assign('errors', $a->getValErrors());
            } else {
                $a->artist_alias = '';
                $a->save(Model::getTable('ArtistItem'), array(), 1);
                $this->redirect('admin/artist/'.$a->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/artist/'.$_POST['id']);
        } else {
            $a = Model::load('ArtistItem');
            $a->id = $_GET['id'];
            $a->load();
            $this->presenter->assign('artist', $a);
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
        $a = Model::load('ArtistItem');
        $a->id = $_GET['id'];
        $a->load();
        $images_removed = false;
        if ($a->image != '') {
            $u = new ImageUpload('', false, array());
            if ($u->remove(array($a->image))) {
                $images_removed = true;
            }
        }
        if ($a->image == '' || $images_removed) {
            $a->delete();
            $this->redirect('admin/artist/0');
        } else {
            $this->redirect('admin/artist/'.$a->id);
        }
    }

    public function edit_bio()
    {
        if (isset($_POST['save'])) {
            $a = Model::load('ArtistItem');
            $a->id = $_POST['id'];
            $a->load();
            $a->bio = $_POST['bio'];
            $a->validates();
            if ($a->hasValErrors()) {
                $this->presenter->assign('artist', $a);
                $this->presenter->assign('errors', $a->getValErrors());
            } else {
                $a->save(Model::getTable('ArtistItem'), array(), 1);
                $this->redirect('admin/artist/'.$_GET['id']);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/artist/'.$_GET['id']);
        }

        $this->buildNav();
        $a = Model::load('ArtistItem');
        $a->id = $_GET['id'];
        $a->load();
        $this->presenter->assign('artist', $a);
    }

    public function upload_image()
    {
        $this->setTemplate('elib://admin/artist.tpl');
        if (isset($_POST['upload'])) {
            $_GET['id'] = $_POST['id'];
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/artist/'.$_POST['id']);
        }

        $a = Model::load('ArtistItem');
        $a->id = $_GET['id'];
        $a->load();

        $this->presenter->assign("artist", $a);

        if (isset($_POST['upload'])) {
            $d = array(array('tn_', 70, 80), array('mid_', 1000, 370));
            $u = new ImageUpload('', true, $d);

            if ($u->error != '') {
                $this->presenter->assign("error", $u->error);
            } else {
                if ($a->image != "") {
                    $u->remove(array($a->image));
                }
                // update db
                $a->image = $u->file;
                $a->save(Model::getTable('ArtistItem'), array(), 2);
                $this->redirect('admin/artist/'.$a->id);
            }
        } else {
            $this->buildNav();
        }
    }

}
