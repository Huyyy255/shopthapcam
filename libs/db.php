<?php

if (!defined('IN_SITE')) {
    die('The Request Not Found');
}
include_once(__DIR__.'/../vendor/autoload.php');
$envPath = __DIR__.'/../.env';
if (file_exists($envPath)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
    $dotenv->load();
}

// Error reporting configuration
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__.'/../api/error_log');

session_start();

class DB
{
    private $ketnoi;
    private $query_count = 0;
    private $query_log = [];
    private $debug_mode = false;
    
    public function __construct()
    {
        $this->debug_mode = (isset($_ENV['DEBUG_MODE']) && $_ENV['DEBUG_MODE'] === 'true') || 
                           (isset($_GET['debug']) && $_GET['debug'] === '1');
    }
    
    public function connect()
    {
        if (!$this->ketnoi) {
            $host = (strpos($_ENV['DB_HOST'], 'p:') === 0) ? $_ENV['DB_HOST'] : 'p:' . $_ENV['DB_HOST'];
            $this->ketnoi = mysqli_connect($host, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']);
            if (!$this->ketnoi) {
                $this->logError('DB Connection Failed: ' . mysqli_connect_error());
                die('Máy chủ đang quá tải, vui lòng thử lại sau');
            }
            mysqli_query($this->ketnoi, "set names 'utf8mb4' ");
            $this->logDebug('Database connected successfully');
        }
    }
    
    private function logDebug($message)
    {
        if ($this->debug_mode) {
            $this->query_log[] = ['time' => microtime(true), 'type' => 'DEBUG', 'message' => $message];
            error_log('[DEBUG] ' . $message);
        }
    }
    
    private function logError($message)
    {
        $this->query_log[] = ['time' => microtime(true), 'type' => 'ERROR', 'message' => $message];
        error_log('[ERROR] ' . $message);
    }
    
    public function getQueryLog()
    {
        return $this->query_log;
    }
    
    public function getQueryCount()
    {
        return $this->query_count;
    }
    
    private function executeQuery($sql, $method = 'query')
    {
        $start = microtime(true);
        $this->connect();
        
        $this->logDebug("SQL [$method]: $sql");
        
        $result = $this->ketnoi->query($sql);
        
        $duration = microtime(true) - $start;
        $this->query_count++;
        
        if ($result === false) {
            $error = mysqli_error($this->ketnoi);
            $errno = mysqli_errno($this->ketnoi);
            $this->logError("SQL Error [$errno]: $error | Query: $sql");
            
            if ($this->debug_mode) {
                echo "<div style='background:#fee;padding:10px;margin:5px;border:1px solid #fcc;border-radius:4px;font-family:monospace;font-size:12px;'>";
                echo "<strong>SQL Error [$errno]:</strong> " . htmlspecialchars($error) . "<br>";
                echo "<strong>Query:</strong> <code>" . htmlspecialchars($sql) . "</code><br>";
                echo "<strong>Method:</strong> $method<br>";
                echo "<strong>Duration:</strong> " . round($duration * 1000, 2) . "ms";
                echo "</div>";
            }
            return false;
        }
        
        $this->logDebug("SQL Success: " . round($duration * 1000, 2) . "ms");
        return $result;
    }
    public function dis_connect()
    {
        if ($this->ketnoi) {
            mysqli_close($this->ketnoi);
        }
    }
    public function site($data)
    {
        $sql = "SELECT * FROM `settings` WHERE `name` = '$data' ";
        $result = $this->executeQuery($sql, 'site');
        if ($result && $row = $result->fetch_array()) {
            return $row['value'];
        }
        return null;
    }
    public function query($sql)
    {
        return $this->executeQuery($sql, 'query');
    }
    public function get_row2($sql)
    {
        return $this->executeQuery($sql, 'get_row2');
    }
    public function cong($table, $data, $sotien, $where)
    {
        $sql = "UPDATE `$table` SET `$data` = `$data` + '$sotien' WHERE $where ";
        return $this->executeQuery($sql, 'cong');
    }
    public function tru($table, $data, $sotien, $where)
    {
        $sql = "UPDATE `$table` SET `$data` = `$data` - '$sotien' WHERE $where ";
        return $this->executeQuery($sql, 'tru');
    }
    public function insert($table, $data)
    {
        $field_list = '';
        $value_list = '';
        foreach ($data as $key => $value) {
            $field_list .= ",$key";
            $value_list .= ",'".mysqli_real_escape_string($this->ketnoi, $value)."'";
        }
        $sql = 'INSERT INTO '.$table. '('.trim($field_list, ',').') VALUES ('.trim($value_list, ',').')';
        $result = $this->executeQuery($sql, 'insert');
        if ($result) {
            return mysqli_insert_id($this->ketnoi);
        }
        return false;
    }
    public function update($table, $data, $where)
    {
        $sql = '';
        foreach ($data as $key => $value) {
            $sql .= "$key = '".mysqli_real_escape_string($this->ketnoi, $value)."',";
        }
        $sql = 'UPDATE '.$table. ' SET '.trim($sql, ',').' WHERE '.$where;
        return $this->executeQuery($sql, 'update');
    }
    public function update_value($table, $data, $where, $value1)
    {
        $sql = '';
        foreach ($data as $key => $value) {
            $sql .= "$key = '".mysqli_real_escape_string($this->ketnoi, $value)."',";
        }
        $sql = 'UPDATE '.$table. ' SET '.trim($sql, ',').' WHERE '.$where.' LIMIT '.$value1;
        return $this->executeQuery($sql, 'update_value');
    }
    public function remove($table, $where)
    {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->executeQuery($sql, 'remove');
    }
    public function get_list($sql)
    {
        $result = $this->executeQuery($sql, 'get_list');
        if (!$result) {
            return [];
        }
        $return = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $return[] = $row;
        }
        mysqli_free_result($result);
        return $return;
    }
    public function get_row($sql)
    {
        $result = $this->executeQuery($sql, 'get_row');
        if (!$result) {
            return false;
        }
        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        if ($row) {
            return $row;
        }
        return false;
    }
    public function num_rows($sql)
    {
        $result = $this->executeQuery($sql, 'num_rows');
        if (!$result) {
            return false;
        }
        $row = mysqli_num_rows($result);
        mysqli_free_result($result);
        if ($row) {
            return $row;
        }
        return false;
    }
    
    public function getLastInsertId()
    {
        return mysqli_insert_id($this->ketnoi);
    }
    
    public function getAffectedRows()
    {
        return mysqli_affected_rows($this->ketnoi);
    }
}

