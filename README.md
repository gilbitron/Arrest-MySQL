#ArrestDB

ArrestDB is a "plug-n-play" RESTful API for SQLite and MySQL databases.

ArrestDB provides a REST API that maps directly to your database stucture with no configuation.

Lets suppose you have set up ArrestDB at `http://api.example.com/` and your database has a table in it called `customers`.

To get a list of customers you would simply need to do:

    GET http://api.example.com/customers/

Where `customers` is the table name. As a response you would get a JSON formatted list of customers.

Or, if you only want to get one customer, then you would append the customer `id` to the URL:

    GET http://api.example.com/customers/123/

##Requirements

- PHP 5.3+ & PDO
- MySQL 5.1+ / SQLite 3.0+

##Installation

Edit `index.php` and change the `$dsn` variable located at the top, here are some examples:

- SQLite: `$dsn = 'sqlite://./path/to/database.sqlite';`
- MySQL: `$dsn = 'mysql://[user[:pass]@]host[:port]/db/;`

Additionally, you may wish to restrict access to specific IP addresses. If so, add them in the `$clients` array:

    $clients = array
	(
		'127.0.0.1',
		'127.0.0.2',
		'127.0.0.3',
	);

After you're done editing the file, place it in a publicly accessible directory (feel free to change the filename to whatever you want).

***Nota bene:*** You must access the file directly, including it from another file won't work.

##API Design

The actual API design is very straightforward and follows the design patterns of the majority of APIs.

    (C)reate > POST   /table
	(R)ead   > GET    /table[/id]
	(R)ead   > GET    /table[/column/content]
	(U)pdate > PUT    /table/id
	(D)elete > DELETE /table/id

To put this into practice below are some example of how you would use the ArrestDB API:

    # Get all rows from the "customers" table
	GET http://api.example.com/customers/

	# Get a single row from the "customers" table (where "123" is the ID)
	GET http://api.example.com/customers/123/

	# Get all rows from the "customers" table where the "country" field matches "Australia" (`LIKE`)
	GET http://api.example.com/customers/country/Australia/

	# Get 50 rows from the "customers" table
	GET http://api.example.com/customers/?limit=50

	# Get 50 rows from the "customers" table ordered by the "date" field
	GET http://api.example.com/customers/?limit=50&by=date&order=desc

	# Create a new row in the "customers" table where the POST data corresponds to the database fields
	POST http://api.example.com/customers/

	# Update customer "123" in the "customers" table where the PUT data corresponds to the database fields
	PUT http://api.example.com/customers/123/

	# Delete customer "123" from the "customers" table
	DELETE http://api.example.com/customers/123/

Please note that `GET` calls accept the following query string variables:

- `by` (column to order by)
  - `order` (order direction: `ASC` or `DESC`)
- `limit` (`LIMIT x` SQL clause)
  - `offset` (`OFFSET x` SQL clause)

Additionally, `POST` and `PUT` requests accept JSON-encoded and/or zlib-compressed payloads.

##Responses

All responses are in the JSON format. For example a `GET` response from the `customers` table might look like:

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


Successful `POST`, `PUT`, and `DELETE` responses will look like:

    {
	    "success": {
	        "code": 200,
	        "status": "OK"
	    }
	}

Errors are expressed in the format:

    {
	    "error": {
	        "code": 204,
	        "status": "No Content"
	    }
	}

The following codes and message are avaiable:

* `200` OK
* `204` No Content
* `400` Bad Request
* `403` Forbidden
* `404` Not Found
* `503` Service Unavailable

##Todo

- ~~support for JSON payloads in `POST` and `PUT` (optionally gzipped)~~
- support for bulk inserts in `POST`
- support for HTTP method overrides
- support for JSON-P responses

##Credits

ArrestDB is a complete rewrite of [Arrest-MySQL](https://github.com/gilbitron/Arrest-MySQL) with several additional features and optimizations.

##License (MIT)

Copyright (c) 2013 Alix Axel (alix.axel@gmail.com).
