<?php
if (!defined('PUN')) {
    exit();
}
define('PUN_TEMPLATE', 1);


require_once 'Smarty/Smarty.class.php';

class PunTemplate extends Smarty
{
    /**
     * Конструктор
     *
     * @param string $punDesignName
     */
    public function __construct($punDesignName)
    {
        parent::__construct();

        $dir = dirname(__FILE__);

        //$root = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', $dir)) . '/template/wap/' . $punDesignName . '/';

        $punDesignDir = $dir . '/template/wap/' . $punDesignName;

        $this->setTemplateDir($punDesignDir . '/tpls/')
            ->setCompileDir($punDesignDir . '/compiled/')
            ->setConfigDir($punDesignDir . '/configs/')
            ->setCacheDir($punDesignDir . '/cache/');

        $this->compile_check = true; // dev mode

        //$this->assign('punDesignDir', $root);
    }
}
