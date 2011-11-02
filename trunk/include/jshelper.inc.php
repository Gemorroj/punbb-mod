<?php
class _jsHelper{
    var $js = array();
    var $jsInt = array();

    function _jsHelper()
    {
        
    }


    function add($path)
    {
        if (!in_array($path,$this->js)) {
            $this->js[] = $path;
        }
    }

 
    function addInternal($script)
    {
        if (!in_array($script,$this->js)) {
            $this->jsInt[] = $script;
        }
    }


    function headerOut()
    {
        $str = null;
        foreach ($this->js as $file) {
            $str .= '<script type="text/javascript" src="' . $file . '"></script>';
        }
        foreach ($this->jsInt as $script) {
            $str .= '<script type="text/javascript">' . $script . '</script>';
        }
        return $str;
    }
}

$jsHelper = new _jsHelper();
?>