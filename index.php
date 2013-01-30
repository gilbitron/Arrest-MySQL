<?php

require('config.php');
require('lib/arrest-mysql.php');

try {

    /**
     * Note: You will need to provide a base_uri as the second param if this file 
     * resides in a subfolder e.g. if the URL to this file is http://example.com/some/sub/folder/index.php
     * then the base_uri should be "some/sub/folder"
     */
    $arrest = new ArrestMySQL($db_config);
    
    /**
     * By default it is assumed that the primary key of a table is "id". If this
     * is not the case then you can set a custom index by using the
     * set_table_index($table, $field) method
     */
    //$arrest->set_table_index('my_table', 'some_index');
    
    $arrest->rest();
    
} catch (Exception $e) {
    echo $e;
}

?>