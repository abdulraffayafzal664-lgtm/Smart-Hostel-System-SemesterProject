<?php
require "config.php";
header("Location: " . (isset($_SESSION["role"]) ? home_for($_SESSION["role"]) : "login.php"));
