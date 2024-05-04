<?php 
require("classes/bdd.class.php");
print_r($bdd->query("SHOW DATABASES"));
