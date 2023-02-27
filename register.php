<?php include("./config.php"); ?>
<?php require_once(ROOT_PATH . "/functions/registration_login.php") ?>
<?php $data = registerUser() ?>
<?php



echo json_encode($data);