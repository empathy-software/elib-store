<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\Storage\Property;
use Empathy\ELib\Storage\PropertyOption;
use Empathy\MVC\Model;

class PropertiesController extends AdminController
{
    public function default_event(): void
    {
        $this->setTemplate('elib://admin/properties.tpl');
        $p = Model::load(Property::class);
        $properties = $p->getAllWithOptions([]);
        $this->assign('properties', $properties);

        if (isset($_POST['add_option'])) {
            if (isset($_POST['id']) && is_numeric($_POST['id'])) {
                $o = Model::load(PropertyOption::class);
                $o->property_id = (int) $_POST['id'];
                $o->option_val = $_POST['option'];
                $o->validates();
                if ($o->hasValErrors()) {
                    $this->assign('submitted_option', $o);
                    $this->assign('errors', $o->getValErrors());
                } else {
                    $o->insert();
                    $this->redirect('admin/properties');
                }
            }
        }

        if ($this->isXMLHttpRequest()) {
            $return_code = 1;
            if (isset($_POST['id']) && is_numeric($_POST['id'])) {
                $o = Model::load(PropertyOption::class);
                $o->load($_POST['id']);
                $o->option_val = $_POST['value'];
                $o->validates();
                if ($o->hasValErrors()) {
                    $return_code = 2;
                } else {
                    $o->save();
                    $return_code = 0;
                }
            }
            header('Content-type: application/json');
            echo json_encode($return_code);
            exit();
        }
    }

    public function add(): void {
        $this->setTemplate('elib://admin/properties.tpl');
        $p = Model::load(Property::class);
        $p->name = '#New Property';
        $p->insert();
        $this->redirect('admin/properties');
    }

    public function rename(): void {
        $this->setTemplate('elib://admin/properties.tpl');
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            if (isset($_POST['save'])) {
                $p = Model::load(Property::class);
                $p->load($_GET['id']);
                $p->name = $_POST['name'];
                $p->validates();
                if ($p->hasValErrors()) {
                    $this->assign('property', $p);
                    $this->assign('errors', $p->getValErrors());
                } else {
                    $p->save();
                    $this->redirect('admin/properties');
                }
            } elseif (isset($_POST['cancel'])) {
                $this->redirect('admin/properties');

            } else {
                $p = Model::load(Property::class);
                $p->load($_GET['id']);
                $this->assign('property', $p);
            }
        }
    }
}
