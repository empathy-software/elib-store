<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\Storage\ArtistItem;
use Empathy\MVC\Model;

class ArtistController extends AdminController
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

    public function toggle_active(): void {
        $a = Model::load(ArtistItem::class);
        $a->load($_GET['id']);
        $a->active = ($a->active) ? 0 : 1;
        $a->save();
        $this->redirect('admin/artist/'.$a->id);
    }

    public function buildNav(): void {
        $this->setTemplate('elib://admin/artist.tpl');
        //$this->assertID();

        $a = Model::load(ArtistItem::class);
        $a->load($_GET['id']);

        $at = new ArtistsTree($a);
        $this->assign('banners', $at->getMarkup());
        $this->assign('artist', $a);
    }

    public function add(): void {
        if (isset($_POST['save'])) {
            $a = Model::load(ArtistItem::class);
            //$a->artist_alias = $_POST['artist_alias'];
            $a->forename = $_POST['forename'];
            $a->surname = $_POST['surname'];
            $a->validates();
            if ($a->hasValErrors()) {
                $this->assign('artist', $a);
                $this->assign('errors', $a->getValErrors());
            } else {
                $a->artist_alias = '';
                $a->active = 0;
                $a->insert();
                $this->redirect('admin/artist/'.$a->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/artist');
        }

        $this->setTemplate('elib://admin/add_artist.tpl');
    }

    public function rename(): void {
        $this->buildNav();
        if (isset($_POST['save'])) {
            $a = Model::load(ArtistItem::class);
            $a->load($_POST['id']);
            //$a->artist_alias = $_POST['artist_alias'];
            $a->forename = $_POST['forename'];
            $a->surname = $_POST['surname'];
            $a->validates();
            if ($a->hasValErrors()) {
                $this->assign('artist', $a);
                $this->assign('errors', $a->getValErrors());
            } else {
                $a->artist_alias = '';
                $a->save();
                $this->redirect('admin/artist/'.$a->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/artist/'.$_POST['id']);
        } else {
            $a = Model::load(ArtistItem::class);
            $a->load($_GET['id']);
            $this->assign('artist', $a);
        }
    }

    public function assertID(): void {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_GET['id'] = 0;
        }
    }

    public function delete(): void {
        $this->assertID();
        $a = Model::load(ArtistItem::class);
        $a->load($_GET['id']);
        $images_removed = false;
        if ($a->image !== '') {
            $u = new ImageUpload('', false, []);
            if ($u->remove([$a->image])) {
                $images_removed = true;
            }
        }
        if ($a->image === '' || $images_removed) {
            $a->delete();
            $this->redirect('admin/artist/0');
        } else {
            $this->redirect('admin/artist/'.$a->id);
        }
    }

    public function edit_bio(): void {
        if (isset($_POST['save'])) {
            $a = Model::load(ArtistItem::class);
            $a->load($_POST['id']);
            $a->bio = $_POST['bio'];
            $a->validates();
            if ($a->hasValErrors()) {
                $this->assign('artist', $a);
                $this->assign('errors', $a->getValErrors());
            } else {
                $a->save();
                $this->redirect('admin/artist/'.$_GET['id']);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/artist/'.$_GET['id']);
        }

        $this->buildNav();
        $a = Model::load(ArtistItem::class);
        $a->load($_GET['id']);
        $this->assign('artist', $a);
    }

    public function upload_image(): void {
        $this->setTemplate('elib://admin/artist.tpl');
        if (isset($_POST['upload'])) {
            $_GET['id'] = $_POST['id'];
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/artist/'.$_POST['id']);
        }

        $a = Model::load(ArtistItem::class);
        $a->load($_GET['id']);

        $this->assign('artist', $a);

        if (isset($_POST['upload'])) {
            $d = [['tn_', 70, 80], ['mid_', 1000, 370]];
            $u = new ImageUpload('', true, $d);

            if ($u->error !== '') {
                $this->assign('error', $u->error);
            } else {
                if ($a->image !== '') {
                    $u->remove([$a->image]);
                }
                // update db
                $a->image = $u->file;
                $a->save();
                $this->redirect('admin/artist/'.$a->id);
            }
        } else {
            $this->buildNav();
        }
    }

}
