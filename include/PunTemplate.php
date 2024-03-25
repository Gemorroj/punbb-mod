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
            ->registerPlugin('modifier', 'get_title', 'get_title')
        ;

        // $this->compile_check = true; // dev mode
        // $this->assign('punDesignDir', $root);
    }
}
