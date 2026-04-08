<?php
require_once '../config/constants.php';

session_destroy();
redirect(BASE_URL . 'index.php');
?>
