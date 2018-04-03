<?php
if(_SERVER("REQUEST_METHOD"))
	exit; // avoid php execution via http request

include_once "/lib/sd_340.php";
include_once "/lib/sn_tcp_ws.php";
include_once "gatekeeper.php";

define ("WS_TCP_ID", 1);
define ("WS_WEB_ACCESS_CONTROL", 2);

define ("PC_PWR_BTN", 0);
define ("PC_RST_BTN", 1);
define ("PC_PWR_LED", 2);

define ("MB_PWR_BTN_LED", 6);
define ("MB_RST_BTN_LED", 7);
define ("MB_PWR_LED", 8);
define ("MB_ETC_LED", 9);

uio_setup(0, PC_PWR_BTN, "out");
uio_setup(0, PC_RST_BTN, "out");
uio_setup(0, PC_PWR_LED, "in");

uio_setup(0, MB_PWR_BTN_LED, "out");
uio_setup(0, MB_RST_BTN_LED, "out");
uio_setup(0, MB_PWR_LED, "out");
uio_setup(0, MB_ETC_LED, "out");

uio_out(0, MB_PWR_BTN_LED, HIGH);
uio_out(0, MB_RST_BTN_LED, HIGH);
uio_out(0, MB_PWR_LED, HIGH);
uio_out(0, MB_ETC_LED, HIGH);

auth_setup(WS_WEB_ACCESS_CONTROL);
ws_setup(WS_TCP_ID, "sample", "text");
$rbuf = "";

$pwr_led_old = -1;

/*
 * data format: "button_type(pwr/rst) 0/1\r\n"
 */
 
function do_button_out($rbuf)
{
	echo "cmd: $rbuf\r\n";
	$cmd = array();
	$cmd = explode(" ", $rbuf, 2);

	if($cmd[0] == "pwr")
	{
		if($cmd[1] == "1")
		{
			echo "POWER button pressed\r\n";
			uio_out(0, PC_PWR_BTN, HIGH);
			uio_out(0, MB_PWR_BTN_LED, LOW);
		}
		else if($cmd[1] == "0")
		{
			echo "POWER button released\r\n";
			uio_out(0, PC_PWR_BTN, LOW);
			uio_out(0, MB_PWR_BTN_LED, HIGH);
		}
	}
	else if($cmd[0] == "rst")
	{
		if($cmd[1] == "1")
		{
			echo "RESET button pressed\r\n";
			uio_out(0, PC_RST_BTN, HIGH);
			uio_out(0, MB_RST_BTN_LED, LOW);
		}
		else if($cmd[1] == "0")
		{
			echo "RESET button released\r\n";
			uio_out(0, PC_RST_BTN, LOW);
			uio_out(0, MB_RST_BTN_LED, HIGH);
		}
	}
}

function do_power_led()
{
	global $pwr_led_old;

	$pwr_led = uio_in(0, PC_PWR_LED);
	if($pwr_led != $pwr_led_old)
	{
		echo "POWER LED = $pwr_led\r\n";
		if($pwr_led == 0) $str = "1";
		else $str = "0";
		ws_write(WS_TCP_ID, $str);
		$pwr_led_old = $pwr_led;
	}
}

while(1)
{
	global $pwr_led_old;
	
	auth_loop();

	if(ws_state(WS_TCP_ID) == TCP_CONNECTED)
	{
		$rlen = ws_read_line(WS_TCP_ID, $rbuf);
		if($rlen > 0)
		{
			echo "ws_read_line: ";
			hexdump($rbuf);

			do_button_out(substr($rbuf, 0, 5));
		}
		

		do_power_led();
	}
	else
	{
		$pwr_led_old = -1;
	}
}
?>