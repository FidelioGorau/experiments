Test Bugtracker
=======================
Simple bugtracher on ZF2.

Installation
------------
For install you need:

1. git clone https://github.com/FidelioGorau/experiments.git 

2. At the root of the project you need run the command:
~~~
	./vendor/bin/doctrine-module orm:schema-tool:update --force
~~~
3.Insert in you DB this SQL script:
~~~
	 INSERT INTO `role` 
	    (`id`, `parent_id`, `roleId`) 
	VALUES
	    (1, NULL, 'guest'),
	    (2, 1, 'user');
~~~


