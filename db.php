<?php 
require 'libs/rb.php';
R::setup( 'mysql:host=localhost;dbname=','', '' );

if ( !R::testconnection() ) {
		exit ('Не удалось создать подключение к БД!');
}

session_start();