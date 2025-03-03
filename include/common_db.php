<?php

// Make sure no one attempts to run this script "directly"
if (!\defined('PUN')) {
    exit;
}

// Коннект с MySQL
class common_db
{
    /**
     * @var string|null
     */
    public $prefix;
    /**
     * @var mysqli
     */
    private $link_id;
    /**
     * @var bool|mysqli_result|null
     */
    private $query_result;
    /**
     * @var array
     */
    private $saved_queries = [];
    /**
     * @var int
     */
    private $num_queries = 0;

    /**
     * @param string $db_host
     * @param string $db_username
     * @param string $db_password
     * @param string $db_name
     */
    public function __construct($db_host, $db_username, $db_password, $db_name)
    {
        $this->link_id = \mysqli_connect($db_host, $db_username, $db_password, $db_name);

        if (!$this->link_id) {
            \error('Unable to connect to MySQL server. MySQL reported: '.\mysqli_connect_error(), __FILE__, __LINE__);
        }
    }

    /**
     * @param string $sql
     *
     * @return bool|mysqli_result
     */
    public function query($sql)
    {
        $stat = \defined('PUN_SHOW_QUERIES');
        if ($stat) {
            $q_start = \microtime(true);
        }

        $this->query_result = \mysqli_query($this->link_id, $sql);

        if ($this->query_result) {
            if ($stat) {
                $this->saved_queries[] = [$sql, \sprintf('%.5f', \microtime(true) - $q_start)];
            }

            ++$this->num_queries;

            return $this->query_result;
        }
        if ($stat) {
            $this->saved_queries[] = [$sql, 0];
        }

        return false;
    }

    /**
     * @param mysqli_result $query_id
     * @param int           $row
     *
     * @return bool
     */
    public function result($query_id, $row = 0)
    {
        if ($query_id && $query_id->num_rows) {
            $query_id->data_seek($row);
            $result = $query_id->fetch_row();

            return $result[0];
        }

        return false;
    }

    /**
     * @param mysqli_result $query_id
     *
     * @return array|bool
     */
    public function fetch_assoc($query_id)
    {
        return $query_id ? $query_id->fetch_assoc() : false;
    }

    /**
     * @param mysqli_result $query_id
     *
     * @return array|bool
     */
    public function fetch_row($query_id)
    {
        return $query_id ? $query_id->fetch_row() : false;
    }

    /**
     * @param mysqli_result $query_id
     *
     * @return bool|int
     */
    public function num_rows($query_id)
    {
        return $query_id ? $query_id->num_rows : false;
    }

    /**
     * @return bool|int
     */
    public function affected_rows()
    {
        return $this->link_id ? $this->link_id->affected_rows : false;
    }

    /**
     * @return bool|int
     */
    public function insert_id()
    {
        return $this->link_id ? $this->link_id->insert_id : false;
    }

    /**
     * @return int
     */
    public function get_num_queries()
    {
        return $this->num_queries;
    }

    /**
     * @return array
     */
    public function get_saved_queries()
    {
        return $this->saved_queries;
    }

    /**
     * @param mysqli_result $query_id
     */
    public function free_result($query_id): void
    {
        if ($query_id) {
            $query_id->free_result();
        }
    }

    /**
     * @param string $str
     *
     * @return string
     */
    public function escape($str)
    {
        return \mysqli_real_escape_string($this->link_id, $str);
    }

    /**
     * @return array
     */
    public function error()
    {
        $saved_queries = $this->get_saved_queries();
        /** @var array|false $lastQuery */
        $last_query = \end($saved_queries);
        if ($last_query) {
            $last_query = \current($last_query);
        }

        return [
            'error_sql' => $last_query ?: '',
            'error_no' => $this->link_id ? $this->link_id->errno : '',
            'error_msg' => $this->link_id ? $this->link_id->error : '',
        ];
    }

    /**
     * @return bool
     */
    public function close()
    {
        if ($this->link_id) {
            // if ($this->query_result) {
            //    $this->query_result->free_result();
            // }

            return $this->link_id->close();
        }

        return false;
    }
}

// Create the database adapter object (and open/connect to/select db)
$db = new DBLayer($db_host, $db_username, $db_password, $db_name);
