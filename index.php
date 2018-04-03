<?php
$buf = _POST("auth");
if($buf != "abcde")
{
	$host = _SERVER("HTTP_HOST");
	header("HTTP/1.1 302 FOUND");
	header("Location: http://$host/web_access_control.php");

	echo "Redirecting to http://$host/web_access_control.php";
}
?>
<!DOCTYPE html>
<html>
<head>
<title>PC Control</title>
<meta name="viewport" content="width=device-width, initial-scale=0.7">
<style> body { text-align: center; } </style>
<script>

var ws;
function init()
{
	var pwr_btn = document.getElementById("pwr_btn");
	var rst_btn = document.getElementById("rst_btn");
	var led = document.getElementById("led");

	pwr_btn.width = 95;
	pwr_btn.height = 95;

	rst_btn.width = 95;
	rst_btn.height = 95;

	pwr_btn.addEventListener("touchstart", pwr_mouse_down);
	pwr_btn.addEventListener("touchend", pwr_mouse_up);
	pwr_btn.addEventListener("mousedown", pwr_mouse_down);
	pwr_btn.addEventListener("mouseup", pwr_mouse_up);
	pwr_btn.addEventListener("mouseout", pwr_mouse_up);

	rst_btn.addEventListener("touchstart", rst_mouse_down);
	rst_btn.addEventListener("touchend", rst_mouse_up);
	rst_btn.addEventListener("mousedown", rst_mouse_down);
	rst_btn.addEventListener("mouseup", rst_mouse_up);
	rst_btn.addEventListener("mouseout", rst_mouse_up);

	update_pwr_btn(0);
	update_rst_btn(0);
	update_led(0);

	ws = new WebSocket("ws://<?echo _SERVER("HTTP_HOST")?>/sample", "text");
	document.getElementById("ws_state").innerHTML = "CONNECTING";

	ws.onopen  = function(){ document.getElementById("ws_state").innerHTML = "<font color='blue'>CONNECTED</font>" };
	ws.onclose = function(){ document.getElementById("ws_state").innerHTML = "<font color='gray'>CLOSED</font>"};
	ws.onerror = function(){ alert("websocket error " + this.url) };

	ws.onmessage = ws_onmessage;
}
function ws_onmessage(e_msg)
{
	e_msg = e_msg || window.event; // MessageEvent

	if(e_msg.data == "1") update_led(1);
	else if(e_msg.data == "0") update_led(0);
	//alert("msg : " + e_msg.data);
}
function update_pwr_btn(state)
{
	var pwr_btn = document.getElementById("pwr_btn");

	if(state)
		pwr_btn.style.backgroundImage = "url('/pwr_on.png')";
	else
		pwr_btn.style.backgroundImage = "url('/pwr_off.png')";
}
function update_rst_btn(state)
{
	var rst_btn = document.getElementById("rst_btn");

	if(state)
		rst_btn.style.backgroundImage = "url('/pwr_on.png')";
	else
		rst_btn.style.backgroundImage = "url('/pwr_off.png')";
}
function pwr_mouse_down()
{
	if(ws.readyState == 1)
		ws.send("pwr 1\r\n");

	update_pwr_btn(1);

	event.preventDefault();
}
function pwr_mouse_up()
{
	if(ws.readyState == 1)
		ws.send("pwr 0\r\n");

	update_pwr_btn(0);
}
function rst_mouse_down()
{
	if(ws.readyState == 1)
		ws.send("rst 1\r\n");

	update_rst_btn(1);

	event.preventDefault();
}
function rst_mouse_up()
{
	if(ws.readyState == 1)
		ws.send("rst 0\r\n");

	update_rst_btn(0);
}
function update_led(state)
{
	var led = document.getElementById("led");

	if(state)
	{
		led.src = "led_on.png";
		//led.style.backgroundImage = "url('/led_on.png')";
		//led.style.height = "200";
	}
	else
	{
		led.src = "led_off.png";
		//led.style.backgroundImage = "url('/led_off.png')";
		//led.style.height = "200";
	}

}
window.onload = init;
</script>
</head>
<body>

<h2>
PC Control<br>

<br>

<canvas id="pwr_btn"></canvas>
<br>
<canvas id="rst_btn"></canvas>
<br>
<img id="led" src="led_on.png" width="50" height="50">

<p>
<span id="ws_state"><font color='gray'>CLOSED</font></span><br>
<!--
ADC : <span id="debug"></span><br>
-->
</p>

</h2>

</body>
</html>

