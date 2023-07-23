<?php

namespace Empathy\ELib\Store;

use Empathy\ELib\Tree;
use Empathy\MVC\Config;

class BrandsTree extends Tree
{
    private $brand;
    private $data;
    private $brand_ancestors;

    public function __construct($brand)
    {
        $this->brand = $brand;
        $this->brand_ancestors = array(0);

        $current_id = $this->brand->id;
        array_push($this->brand_ancestors, $current_id);

        $this->data = $this->buildTree(0, $this);
        $this->markup = $this->buildMarkup($this->data, 0, $current_id, 0);
    }

    public function buildTree($id, $tree)
    {
        $nodes = array();
        $nodes = $tree->brand->buildTree($id, $tree);

        return $nodes;
    }

    private function buildMarkup($data, $level, $current_id, $last_id)
    {
        $markup = "\n<ul";

        $ancestors = $this->brand_ancestors;

        if (!in_array($last_id, $ancestors)) {
            $markup .= " class=\"hidden_sections\"";
        }
        if ($level == 0) {
            $markup .= " id=\"tree\"";
            $level++;
        }
        $markup .=">\n";
        foreach ($data as $index => $value) {
            $toggle = '+';
            //$folder = 't_folder_closed.gif';
            $folder = 'data.gif';
            $url = 'brand';

            if (in_array($value['id'], $ancestors)) {
                $toggle = '-';
                //	    $folder = 't_folder_open.gif';
            }

            if (isset($value['banner']) && $value['banner'] == 1) {
                $folder = 'data.gif';
                $url = 'banner';
            }

            $value['label'] = htmlentities($value['name']);

            //$children = sizeof($value['children']);
            $children = 0;
            $markup .= "<li";

            if ($current_id == $value['id']) {
                $markup .= " class=\"current\"";
            }

            $markup .= ">\n";
            if ($children > 0) {
                $markup .= "<a class=\"toggle\" href=\"http://".Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/admin/$url/".$value['id'];
                if ($toggle == '-') {
                    $markup .= '/?collapsed=1';
                }
                $markup .= "\">$toggle</a>";
            } else {
                $markup .= "<span class=\"toggle\">&nbsp;</span>";
            }
            $markup .= "<img src=\"http://".Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/elib/$folder\" alt=\"\" />\n";

            if ($current_id == $value['id']) {
                $markup .= "<span class=\"label current\">".$value['label']."</span>";
            } else {
                $markup .= "<span class=\"label\"><a href=\"http://".Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/admin/$url/".$value['id']."\">".$value['label']."</a></span>";
            }
            if ($children > 0) {
                $markup .= $this->buildMarkup($value['children'], $level, $current_id, $value['id'], $value['banner'], $current_is_dir);
            }
            $markup .= "</li>\n";
        }
        $markup .= "</ul>\n";

        return $markup;
    }
}
