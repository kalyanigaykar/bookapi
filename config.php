<?php
define ( 'DB_HOST', 'localhost' );
define ( 'DB_USER', 'root' );
define ( 'DB_PASSWORD', '' );
define ( 'DB_DB', 'gutendex' );
$db = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_DB);
/*define ( 'DB_HOST', '127.0.0.1' );
define ( 'DB_USER', 'gutendex' );
define ( 'DB_PASSWORD', 'gutendex' );
define ( 'DB_DB', 'gutendex' );
$db = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_DB);*/
?>