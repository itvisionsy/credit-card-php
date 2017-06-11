<?php

use ItvisionSy\CreditCard\CreditCard;

require_once "../vendor/autoload.php";

$card = new CreditCard("4005550000000001", "", "1705", "");
$card->cardType();