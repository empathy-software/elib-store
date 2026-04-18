<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\Storage\ProductItem;
use Empathy\ELib\Storage\ShippingAddress;
use Empathy\ELib\Storage\UserItem;
use Empathy\MVC\DI;
use Empathy\MVC\Model;

class VendorsController extends AdminController
{
    public function default_event(): void
    {
        $vendorModel = DI::getContainer()->get('VendorModel');


        if (isset($_POST['verify'])) {
            $v = Model::load($vendorModel);
            $v->id = $_POST['vendor_id'];
            $v->load($v->id);

            $u = Model::load(UserItem::class);
            $u->id = $v->user_id;
            $u->load($u->id);

            if ($u->active && $v->name !== '') {
                $u->auth = Access::VENDOR;
                $u->save();

                $v->verified = 'MYSQLTIME';
                $v->save();

                $p = Model::load(ProductItem::class);
                $p->verify($v->id);
            }
            $this->redirect('admin/vendors');
        } else {
            $u = Model::load(UserItem::class);
            $select = '*,UNIX_TIMESTAMP(t1.registered) AS registered, t2.id as vendor_id';
            $t1 = Model::getTable(UserItem::class);
            $t2 = Model::getTable($vendorModel);
            $t3 = Model::getTable(ShippingAddress::class);
            $sql = ' WHERE t1.id = t2.user_id AND t1.id = t3.user_id AND t3.default_address = 1';
            $page = 1;
            $per_page = 10;

            $vendors = $u->getAllCustomPaginateMultiJoin($select, $t2, $t3, $sql, $page, $per_page);
            $paginate = $u->getPaginatePagesMultiJoin($select, $t2, $t3, $sql, $page, $per_page);

            $this->assign('vendors', $vendors);
            $this->assign('paginate', $paginate);

            $this->setTemplate('elib://admin/vendors.tpl');
        }
    }

}
