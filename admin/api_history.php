<?php
require_once("../Controllers/UserController.php");
require_once("authentificate.php");


 $history = new UserController();
 $result = $history->ApiHistory();
 echo $result;

?>