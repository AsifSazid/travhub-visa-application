<?php

$pdo = new PDO("mysql:host=localhost;dbname=visa_application", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
