<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
header("Access-Control-Allow-Origin: *");
include('include/config.php');

//Router
include("../config/router.php");
