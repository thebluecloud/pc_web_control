<?php

if(_SERVER("REQUEST_METHOD"))
	exit; // avoid php execution via http request

include_once "/lib/sn_tcp_ws.php";

$ws_rbuf = "";
$authenticated = false;
$auth_pattern = "1,4,8,6,3";
$ws_ac_id = 0;
$um_pid = 0;

function auth_setup($ws_id)
{
	global $ws_ac_id;

	$ws_ac_id = $ws_id;
	ws_setup($ws_ac_id, "web_pattern", "text.phpoc");
}

function get_auth_state()
{
	global $authenticated;
	return $authenticated;
}

function set_auth_state($st)
{
	global $authenticated;

	if($st == true) $authenticated = true;
	else $authenticated = false;

	return $authenticated;
}

function auth_loop()
{
	global $ws_ac_id, $ws_rbuf, $auth_pattern;

	if(ws_state($ws_ac_id) == TCP_CONNECTED)
	{
		$rlen = ws_read_line($ws_ac_id, $ws_rbuf);
		
		if($rlen)
		{
			$array = explode(" ",$ws_rbuf);
			$cmd = (int) $array[0];
			$data = rtrim($array[1], "\r\n");
			
			if($cmd == 0)
			{
				// simple authentication
				if($auth_pattern == $data)
				{
					set_auth_state(true);
					ws_write($ws_ac_id, "202\r\n");
				}
				else
				{
					set_auth_state(false);
					ws_write($ws_ac_id, "401\r\n");
				}
			}
		}
	}
}
 
?>