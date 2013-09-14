<?php
class JsHelper
{
    protected $js = array();
    protected $jsInternal = array();

    /**
     * @var JsHelper
     */
    private static $instance;

    private function __construct(){}
    private function __clone(){}
    private function __wakeup(){}


    /**
     * getInstance
     *
     * @return JsHelper
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new JsHelper();
        }

        return self::$instance;
    }

    /**
     * @param string $path
     */
    public function add($path)
    {
        if (!in_array($path, $this->js)) {
            $this->js[] = $path;
        }
    }


    /**
     * @param string $script
     */
    public function addInternal($script)
    {
        if (!in_array($script, $this->js)) {
            $this->jsInternal[] = $script;
        }
    }


    /**
     * @return string
     */
    public function headerOut()
    {
        $str = '';
        foreach ($this->js as $file) {
            $str .= '<script type="application/javascript" src="' . $file . '"></script>';
        }
        foreach ($this->jsInternal as $script) {
            $str .= '<script type="application/javascript">' . $script . '</script>';
        }
        return $str;
    }

    /**
     * @return int
     */
    public function count()
    {
        return sizeof($this->js);
    }

    /**
     * @return int
     */
    public function countInternal()
    {
        return sizeof($this->jsInternal);
    }
}
