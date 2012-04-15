<?php if (! defined('PUN')) exit(); define('PUN_TEMPLATE', 1);

require_once('Smarty/Smarty.class.php');

class PunTemplate extends Smarty
{
    public function __construct($punDesignName)
    {
        parent::__construct();
        
        $root = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', dirName(__FILE__))) . '/template/wap/' . $punDesignName . '/';
        
        $punDesignDir = dirName(__FILE__) . '/template/wap/' . $punDesignName;
        
        $this->template_dir = $punDesignDir . '/tpls/';
        $this->compile_dir  = $punDesignDir . '/compiled/';
        $this->config_dir   = $punDesignDir . '/configs/';
        $this->cache_dir    = $punDesignDir . '/cache/';
        
        $this->assign('punDesignDir', $root);
    }
}