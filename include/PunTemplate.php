<?php

if (!\defined('PUN')) {
    exit;
}
\define('PUN_TEMPLATE', 1);

class PunTemplate extends Smarty\Smarty
{
    /**
     * Конструктор
     *
     * @param string $punDesignName
     */
    public function __construct($punDesignName)
    {
        parent::__construct();

        // $root = str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', __DIR__)) . '/template/wap/' . $punDesignName . '/';

        $punDesignDir = __DIR__.'/template/wap/'.$punDesignName;

        $this->setTemplateDir($punDesignDir.'/tpls/')
            ->setCompileDir($punDesignDir.'/compiled/')
            ->setConfigDir($punDesignDir.'/configs/')
            ->setCacheDir($punDesignDir.'/cache/')
            ->registerPlugin(self::PLUGIN_MODIFIER, 'rawurlencode', 'rawurlencode')
            ->registerPlugin(self::PLUGIN_MODIFIER, 'microtime', 'microtime')
            ->registerPlugin(self::PLUGIN_MODIFIER, 'sprintf', 'sprintf')
            ->registerPlugin(self::PLUGIN_MODIFIER, 'strtok', 'strtok')
            ->registerPlugin(self::PLUGIN_MODIFIER, 'file_exists', 'file_exists')
            ->registerPlugin(self::PLUGIN_MODIFIER, 'range', 'range')
            ->registerPlugin(self::PLUGIN_MODIFIER, 'ceil', 'ceil')
            ->registerPlugin(self::PLUGIN_MODIFIER, 'uniqid', 'uniqid')
            ->registerPlugin(self::PLUGIN_MODIFIER, 'get_title', 'get_title') // functions.php
            ->registerPlugin(self::PLUGIN_MODIFIER, 'paginate', 'paginate') // functions.php
        ;

        // $this->compile_check = true; // dev mode
        // $this->assign('punDesignDir', $root);
    }
}
