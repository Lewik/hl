<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Lewik (lllewik at gmail dot com)
 */


/**
 * You can rename and modify all functions as you wish
 */

/**
 * It's colored vardump
 */
function hl()
{
	hl::say(func_get_args(), debug_backtrace());
}

/**
 * hl analog. dies.
 */
function dhl()
{
	hl::say(func_get_args(), debug_backtrace());
	die();
}

/**
 * hl analog, but outputs only in file
 * usefull with ajax
 */
function fhl()
{
	$func_get_args = func_get_args();
	array_unshift($func_get_args, '--f');
	hl::say($func_get_args, debug_backtrace());
}

/**
 * @param null $label name of label
 * @param null $minDelta minimum delta time to echo. This value saves for other t(), no need to write it in each t()
 * @return mixed|string returns delta time
 * write t() in code, where you want to know process time
 */
function t($label = null, $minDelta = null)
{
	return hl::tic($label, $minDelta, debug_backtrace());
}

/**
 * echo debug_backtrace in table
 */
function bt()
{
	$bt = debug_backtrace();
	echo '
		<table>
		<caption>hl debug backtrace<caption>
		<tr>
			<th>call</th>
			<th>place</th>
		</tr>
		';
	unset($bt[0]);
	foreach ($bt as $i => $data) {
		echo '
		<tr>
			<td>' . $data['class'] . $data['type'] . $data['function'] . '</td>
			<td>' . $data['line'] . ':' . $data['file'] . '</td>
		</tr>
		';
	}
	echo '
		</table>
		';

}