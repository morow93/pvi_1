<?php

  $uri    = "mongodb://localhost/billboard";
  $client = new MongoClient($uri);
  $dbname = $client->selectDB('billboard');

?>
