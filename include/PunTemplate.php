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
            ->registerPlugin('modifier', 'rawurlencode', 'rawurlencode')
            ->registerPlugin('modifier', 'microtime', 'microtime')
            ->registerPlugin('modifier', 'sprintf', 'sprintf')
            ->registerPlugin('modifier', 'strtok', 'strtok')
            ->registerPlugin('modifier', 'file_exists', 'file_exists')
            ->registerPlugin('modifier', 'range', 'range')
            ->registerPlugin('modifier', 'ceil', 'ceil')
            ->registerPlugin('modifier', 'uniqid', 'uniqid')
            ->registerPlugin('modifier', 'get_title', 'get_title') // functions.php
            ->registerPlugin('modifier', 'paginate', 'paginate') // functions.php
        ;

        // $this->compile_check = true; // dev mode
        // $this->assign('punDesignDir', $root);
    }
}
