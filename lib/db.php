<?php
/**
 * MySQL Database Wrapper
 * Author: Caleb Mingle (Modified by Gilbert Pellegrom)
 * Website: http://forrst.com/posts/Simple_PHP_MySQL_Wrapper-LZ1
 * Date: 4/22/2011
 */
 
class Database {
    private $_config;       // Stores the configuration provided by user.
    private $_query;        // Stores the current query.
    private $_error;        // Stores an error based on $_verbose.
    private $_verbose;      // Stores a boolean determining output / storage of errors.

    private $_buildQuery;   // Stores the current "in progress" query build.

    public function __construct($config) {
        $this->_config = $config;
    }

    /*
     * Initializes the database.  Checks the configuration, connects, selects database.
     */
    public function init() {
        if(!$this->__check_config()) {
            return false;
        }

        if(!$this->__connect()) {
            return false;
        }

        if(!$this->__select_db()) {
            return false;
        }

        return true;
    }

    /*
     * Checks the configuration for blanks.
     */
    private function __check_config() {
        $config = $this->_config;

        if(empty($config["server"]) || empty($config["username"]) || empty($config["database"])) {
            $this->_error = "Configuration details were blank.";
            return false;
        }

        $this->_verbose = ($config["verbose"]) ? true : false;

        return true;
    }

    /*
     * Connects to the database.
     */
    private function __connect() {
        $connection = @mysql_connect($this->_config["server"], $this->_config["username"], $this->_config["password"]);

        if(!$connection) {
            $this->_error = ($this->_verbose) ? mysql_error() : "Could not connect to database.";
            return false;
        }

        return true;
    }

    /*
     * Selects the database to be working with.
     */
    private function __select_db() {
        $database = @mysql_select_db($this->_config["database"]);

        if(!$database) {
            $this->_error = ($this->_verbose) ? mysql_error() : "Could not select database.";
            return false;
        }

        return true;
    }

    /*
     * SELECT starter.  $fields can be either a string or an array of strings to select.
     */
    public function select($fields) {
        $query = "SELECT";

        if(!empty($fields) && !is_array($fields)) {
            $query .= " {$fields}";
        } else if(is_array($fields)) {
            $query .= " `";
            $query .= implode("`,`", $fields);
            $query .= "`";
        } else {
            $query .= " *";
        }

        $this->_buildQuery = $query;
        return $this;
    }

    /*
     * Adds where the SELECT is going to be coming from (table wise).
     * select("*")
     * select("username")
     * select(array("username", "password"))
     */
    public function from($table) {
        $this->_buildQuery .= " FROM `{$table}`";
        return $this;
    }

    /*
     * UPDATE starter.
     * update("users")
     */
    public function update($table) {
        $this->_buildQuery = "UPDATE `{$table}`";
        return $this;
    }
    
    /*
     * DELETE starter.
     * delete("users")
     */
    public function delete($table) {
        $this->_buildQuery = "DELETE FROM `{$table}`";
        return $this;
    }

    /*
     * INSERT starter.  $data is an array matched columns to values:
     * $data = array("username" => "Caleb", "email" => "caleb@mingle-graphics.com");
     * insert("users", array("username" => "Caleb", "password" => "hash"))
     */
    public function insert($table, $data) {
        $query = "INSERT INTO `{$table}` (";
        $keys   = array_keys($data);
        $values = array_values($data);
        
        $query .= implode(", ", $keys);
        $query .= ") VALUES (";
        
        $array  = array();
        
        foreach($values as $value) {
            $array[] = "'{$value}'";
        }
        
        $query .= implode(", ", $array) . ")";
        
        $this->_buildQuery = $query;
        return $this;
    }

    /*
     * SET.  $data is an array matched key => value.
     * set(array("username" => "Caleb"))
     */
    public function set($data) {
        if(!is_array($data)) return $this;
        
        $query =  "SET ";
        $array = array();

        foreach($data as $key => $value) {
            $array[] = "`{$key}`='{$value}'";
        }

        $query .= implode(", ", $array);

        $this->_buildQuery .= " " . $query;
        return $this;
    }

    /*
     * WHERE.  $fields and $values can either be strings or arrays based on how many you need.
     * $operators can be an array to add in <, >, etc.  Must match the index for $fields and $values.
     * where("username", "Caleb")
     * where(array("username", "password"), array("Caleb", "testing"))
     * where(array("username", "level"), array("Caleb", "10"), array("=", "<"))
     */
    public function where($fields, $values, $operators = '') {
        if(!is_array($fields) && !is_array($values)) {
            $operator = (empty($operators)) ? '=' : $operators[0];
            $query = " WHERE `{$fields}` {$operator} '{$values}'";
        } else {
            $array = array_combine($fields, $values);
            $query = " WHERE ";

            $data  = array();
            $counter = 0;

            foreach($array as $key => $value) {

                $operator = (!empty($operators) && !empty($operators[$counter])) ? $operators[$counter] : '=';

                $data[] = "`{$key}` {$operator} '{$value}'";

                $counter++;
            }

            $query .= implode(" AND ", $data);
        }

        $this->_buildQuery .= $query;
        return $this;
    }

    /*
     * Order By:
     * order_by("username", "asc")
     */
    public function order_by($field, $direction = 'asc') {
        if($field) $this->_buildQuery .= " ORDER BY `{$field}` " . strtoupper($direction);
        return $this;
    }

    /*
     * Limit:
     * limit(1)
     * limit(1, 0)
     */
    public function limit($max, $min = '0') {
        if($max) $this->_buildQuery .= " LIMIT {$min},{$max}";
        return $this;
    }

    /*
     * Will return the object of data from the query.
     */
    public function fetch_object() {
        $object = @mysql_fetch_object($this->_query);

        if(!$object && $this->_verbose) {
            $this->_error = mysql_error();
        }

        return $object;
    }

    /*
     * Will return the array of data from the query.
     */
    public function fetch_array() {
        $array = @mysql_fetch_array($this->_query);
        if($array){
            foreach($array as $key=>$val){ 
                if(is_numeric($key)){ 
                    unset($array[$key]); 
                } 
            }
        }

        if(!$array && $this->_verbose) {
            $this->_error = mysql_error();
        }

        return $array;
    }
    
    public function fetch_all() {
        $results = array();
        while($array = @mysql_fetch_array($this->_query)){  
            foreach($array as $key=>$val){ 
                if(is_numeric($key)){ 
                    unset($array[$key]); 
                } 
            }
            $results[] = $array; 
        }
        


        if(!$array && $this->_verbose) {
            $this->_error = mysql_error();
        }

        return $results;
    }

    /*
     * Will return the number or rows affected from the query.
     */
    public function num_rows() {
        $num = @mysql_num_rows($this->_query);

        if(!$num && $this->_verbose) {
            $this->_error = mysql_error();
        }

        return $num;
    }

    /*
     * If $query_text is blank, query will be performed on the built query stored.
     */
    public function query($query_text = '') {
        $query_text = ($query_text == '') ? $this->_buildQuery : $query_text;
        
        $query = @mysql_query($query_text);
        
        if(!$query && $this->_verbose) {
            echo "<h1>MySQL Error:</h1>";
            echo "<p>" . mysql_error() . "</p>";
        }

        $this->_query = $query;
        
        return $this;
    }

    /*
     * Will return the current built query story in $this->_buildQuery;
     */
    public function get_query() {
        return $this->_buildQuery;
    }

    /*
     * Will return the current stored error.
     */
    public function get_error() {
        return $this->_error;
    }
}

?>