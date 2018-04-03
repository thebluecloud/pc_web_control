<?php
if(_SERVER("REQUEST_METHOD"))
	exit; // avoid php execution via http request

system("php task0.php");

?>