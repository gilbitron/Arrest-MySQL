#Arrest MySQL

Arrest MySQL is a "plug-n-play" RESTful API for your MySQL database. Arrest MySQL provides a REST API that maps directly to your database stucture with no configuation.

For example lets suppose you have set up Arrest MySQL at http://api.example.com and your database has a table in it called "customers". To get a list of customers you would simply need to do:

```GET http://api.example.com/customers```

Where "customers" is the table name. As a response you would get a JSON formatted list of customers. Or say you only want to get one customer, then you would do this:

```GET http://api.example.com/customers/123```

Where "123" here is the ID of the customer. For more information on using Arrest MySQL see the Usage section below.

##Requirements

1. Apache Server with PHP 5+
2. MySQL 5+

##30 Second Installation

Simply put these files into a folder somewhere on your server. Then edit config.php and fill in your database details and you are good to go.

##Usage

If you edit index.php you will see how incredibly simple it is to set up Arrest MySQL. Note that you are left to **provide your own authentication** for your API when using Arrest MySQL.

```php
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
```

###API Design

The actual API design is very straight forward and follows the design patterns of most other API's.

```
create > POST   /table
read   > GET    /table[/id]
update > PUT    /table/id
delete > DELETE /table/id
```

To put this into practice below are some example of how you would use an Arrest MySQL API:

```
// Get all rows from the "customers" table
GET http://api.example.com/customers
// Get a single row from the "customers" table (where "123" is the ID)
GET http://api.example.com/customers/123
// Get 50 rows from the "customers" table
GET http://api.example.com/customers?limit=50
// Get 50 rows from the "customers" table ordered by the "date" field
GET http://api.example.com/customers?limit=50&order_by=date&order=desc

// Create a new row in the "customers" table where the POST data corresponds to the database fields
POST http://api.example.com/customers

// Update customer "123" in the "customers" table where the PUT data corresponds to the database fields
PUT http://api.example.com/customers/123

// Delete customer "123" from the "customers" table
DELETE http://api.example.com/customers/123
```

###Responses

All responses are in the JSON format. For example a GET response from the "customers" table might look like:

```json
[
    {
        "id": "114",
        "customerName": "Australian Collectors, Co.",
        "contactLastName": "Ferguson",
        "contactFirstName": "Peter",
        "phone": "123456",
        "addressLine1": "636 St Kilda Road",
        "addressLine2": "Level 3",
        "city": "Melbourne",
        "state": "Victoria",
        "postalCode": "3004",
        "country": "Australia",
        "salesRepEmployeeNumber": "1611",
        "creditLimit": "117300"
    },
    ...
]
```

Successful POST, PUT, and DELETE responses will look like

```json
{
    "success": {
        "message": "Success",
        "code": 200
    }
}
```

Errors are in the format:

```json
{
    "error": {
        "message": "No Content",
        "code": 204
    }
}
```

The following codes and message are avaiable:

* 200 Success
* 204 No Content
* 404 Not Found

##Credits

Arrest MySQL was created by [Gilbert Pellegrom](http://gilbert.pellegrom.me) from [Dev7studios](http://dev7studios.com).

Please contribute by [reporting bugs](Arrest-MySQL/issues) and submitting [pull requests](Arrest-MySQL/pulls).

##License (MIT)

Copyright © 2013 Dev7studios

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation 
files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, 
modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software 
is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES 
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE 
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR 
IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
