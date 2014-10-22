<?php

// Make sure no one attempts to run this script "directly"
if (!defined('PUN')) {
    exit;
}


// Коннект с MySQL
class DBLayer
{
    public $prefix;
    protected $link_id;
    protected $query_result;

    protected $saved_queries = array();
    protected $num_queries = 0;


    public function __construct($db_host, $db_username, $db_password, $db_name, $p_connect)
    {
        if ($p_connect) {
            $this->link_id = @mysql_pconnect($db_host, $db_username, $db_password);
        } else {
            $this->link_id = @mysql_connect($db_host, $db_username, $db_password);
        }

        if ($this->link_id) {
            // set utf-8
            mysql_set_charset('utf8', $this->link_id);
            if (!@mysql_select_db($db_name, $this->link_id)) {
                error('Unable to select database. MySQL reported: ' . mysql_error($this->link_id), __FILE__, __LINE__);
            }
        } else {
            error('Unable to connect to MySQL server. MySQL reported: ' . mysql_error(), __FILE__, __LINE__);
        }
    }


    public function query($sql, $unbuffered = false)
    {
        $stat = defined('PUN_SHOW_QUERIES');
        if ($stat) {
            $q_start = microtime(true);
        }

        if ($unbuffered) {
            $this->query_result = @mysql_unbuffered_query($sql, $this->link_id);
        } else {
            $this->query_result = @mysql_query($sql, $this->link_id);
        }

        if ($this->query_result) {
            if ($stat) {
                $this->saved_queries[] = array($sql, sprintf('%.5f', microtime(true) - $q_start));
            }

            ++$this->num_queries;

            return $this->query_result;
        } else {
            if ($stat) {
                $this->saved_queries[] = array($sql, 0);
            }

            return false;
        }
    }


    public function result($query_id = 0, $row = 0)
    {
        return ($query_id) ? @mysql_result($query_id, $row) : false;
    }


    public function fetch_assoc($query_id = 0)
    {
        return ($query_id) ? @mysql_fetch_assoc($query_id) : false;
    }


    public function fetch_row($query_id = 0)
    {
        return ($query_id) ? @mysql_fetch_row($query_id) : false;
    }


    public function num_rows($query_id = 0)
    {
        return ($query_id) ? @mysql_num_rows($query_id) : false;
    }


    public function affected_rows()
    {
        return ($this->link_id) ? @mysql_affected_rows($this->link_id) : false;
    }


    public function insert_id()
    {
        return ($this->link_id) ? @mysql_insert_id($this->link_id) : false;
    }


    public function get_num_queries()
    {
        return $this->num_queries;
    }


    public function get_saved_queries()
    {
        return $this->saved_queries;
    }


    public function free_result($query_id = null)
    {
        return ($query_id) ? @mysql_free_result($query_id) : false;
    }


    public function escape($str)
    {
        if (is_array($str)) {
            return '';
        } else {
            return mysql_real_escape_string($str, $this->link_id);
        }
    }


    public function error()
    {
        $result['error_sql'] = @current(@end($this->saved_queries));
        $result['error_no'] = @mysql_errno($this->link_id);
        $result['error_msg'] = @mysql_error($this->link_id);

        return $result;
    }


    public function close()
    {
        if ($this->link_id) {
            if ($this->query_result) {
                @mysql_free_result($this->query_result);
            }

            return @mysql_close($this->link_id);
        } else {
            return false;
        }
    }
}

// Create the database adapter object (and open/connect to/select db)
$db = new DBLayer($db_host, $db_username, $db_password, $db_name, $p_connect);
