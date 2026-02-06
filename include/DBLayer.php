<?php

// Make sure no one attempts to run this script "directly"
if (!\defined('PUN')) {
    exit;
}

// Коннект с MySQL
class DBLayer
{
    public ?string $prefix = null;
    private ?mysqli $link_id = null;
    /**
     * @var bool|mysqli_result
     */
    private $query_result;
    /**
     * @var array{0: string, 1: int|float}
     */
    private array $saved_queries = [];
    private int $num_queries = 0;

    public function __construct(string $db_host, string $db_username, string $db_password, string $db_name)
    {
        $link_id = \mysqli_connect($db_host, $db_username, $db_password, $db_name);

        if (!$link_id) {
            \error('Unable to connect to MySQL server. MySQL reported: '.\mysqli_connect_error(), __FILE__, __LINE__);
        }
        $this->link_id = $link_id;
    }

    public function query(string $sql): bool|mysqli_result
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

    public function result(?mysqli_result $query_id, int $row = 0): bool
    {
        if ($query_id && $query_id->num_rows) {
            $query_id->data_seek($row);
            $result = $query_id->fetch_row();

            return $result[0];
        }

        return false;
    }

    public function fetch_assoc(?mysqli_result $query_id): ?array
    {
        return $query_id ? $query_id->fetch_assoc() : null;
    }

    public function fetch_row(?mysqli_result $query_id): ?array
    {
        return $query_id ? $query_id->fetch_row() : null;
    }

    public function num_rows(?mysqli_result $query_id): ?int
    {
        return $query_id ? $query_id->num_rows : null;
    }

    public function affected_rows(): ?int
    {
        return $this->link_id ? $this->link_id->affected_rows : null;
    }

    public function insert_id(): ?int
    {
        return $this->link_id ? $this->link_id->insert_id : null;
    }

    public function get_num_queries(): int
    {
        return $this->num_queries;
    }

    /**
     * @return array{0: string, 1: int|float}
     */
    public function get_saved_queries(): array
    {
        return $this->saved_queries;
    }

    public function free_result(?mysqli_result $query_id): void
    {
        if ($query_id) {
            $query_id->free_result();
        }
    }

    public function escape(string $str): string
    {
        return \mysqli_real_escape_string($this->link_id, $str);
    }

    /**
     * @return array{error_sql: string, error_no: int, error_msg: string}
     */
    public function error(): array
    {
        $saved_queries = $this->get_saved_queries();
        $last_query = \end($saved_queries);
        if ($last_query) {
            $last_query = \current($last_query);
        }

        return [
            'error_sql' => $last_query ?: '',
            'error_no' => $this->link_id ? $this->link_id->errno : 0,
            'error_msg' => $this->link_id ? $this->link_id->error : '',
        ];
    }

    public function close(): bool
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
