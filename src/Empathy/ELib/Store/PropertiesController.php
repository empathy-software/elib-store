<?php

namespace Empathy\ELib\Store;

use Empathy\ELib\Model;

class PropertiesController extends AdminController
{
    public function default_event()
    {
        $this->setTemplate('elib://admin/properties.tpl');
        $p = Model::load('Property');
        $properties = $p->getAllWithOptions(array());
        $this->presenter->assign('properties', $properties);

        if (isset($_POST['add_option'])) {
            if (isset($_POST['id']) && is_numeric($_POST['id'])) {
                $o = Model::load('PropertyOption');
                $o->property_id = $_POST['id'];
                $o->option_val = $_POST['option'];
                $o->validates();
                if ($o->hasValErrors()) {
                    $this->presenter->assign('submitted_option', $o);
                    $this->presenter->assign('errors', $o->getValErrors());
                } else {
                    $o->insert(Model::getTable('PropertyOption'), 1, array(), 1);
                    $this->redirect('admin/properties');
                }
            }
        }

        if ($this->isXMLHttpRequest()) {
            $return_code = 1;
            if (isset($_POST['id']) && is_numeric($_POST['id'])) {
                $o = Model::load('PropertyOption');
                $o->id = $_POST['id'];
                $o->load();
                $o->option_val = $_POST['value'];
                $o->validates();
                if ($o->hasValErrors()) {
                    $return_code = 2;
                } else {
                    $o->save(Model::getTable('PropertyOption'), array(), 1);
                    $return_code = 0;
                }
            }
            header('Content-type: application/json');
            echo json_encode($return_code);
            exit();
        }
    }

    public function add()
    {
        $this->setTemplate('elib://admin/properties.tpl');
        $p = Model::load('Property');
        $p->name = '#New Property';
        $p->insert(Model::getTable('Property'), 1, array(), 0);
        $this->redirect('admin/properties');
    }

    public function rename()
    {
        $this->setTemplate('elib://admin/properties.tpl');
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            if (isset($_POST['save'])) {
                $p = Model::load('Property');
                $p->id = $_GET['id'];
                $p->load();
                $p->name = $_POST['name'];
                $p->validates();
                if ($p->hasValErrors()) {
                    $this->presenter->assign('property', $p);
                    $this->presenter->assign('errors', $p->getValErrors());
                } else {
                    $p->save(Model::getTable('Property'), array(), 1);
                    $this->redirect('admin/properties');
                }
            } elseif (isset($_POST['cancel'])) {
                $this->redirect('admin/properties');

            } else {
                $p = Model::load('Property');
                $p->id = $_GET['id'];
                $p->load();
                $this->presenter->assign('property', $p);
            }
        }

    }

}
