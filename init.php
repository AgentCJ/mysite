<?php

const DB_HOST = "127.0.0.1";
const DB_PORT = "3306";
const DB_LOGIN = "root";
const DB_PASSWORD = "root";
const DB_NAME = "mysite";

$con = mysqli_connect(DB_HOST, DB_LOGIN, DB_PASSWORD, DB_NAME);
$link = $con;

mysqli_set_charset($con, "utf8");

$is_auth = false;      //$is_auth = rand(0, 1); 
$user_name = '';      //$user_name = 'Сергей'; 

if (!isset($title_name)) {
    $title_name = 'Главная';
}
