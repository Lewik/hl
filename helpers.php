<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Lewik (lllewik at gmail dot com)
 */


/** You can rename and modify all functions as you wish */

/** It's colored vardump */
function hl()
{
    hl::say(func_get_args(), debug_backtrace());
}

/**
 * vardump to error log
 * Use any php log viewer
 */
function ehl()
{
    $func_get_args = func_get_args();
    array_unshift($func_get_args, '--e');
    hl::say($func_get_args, debug_backtrace());
}


/** hl analog. dies. */
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
function t($label = null, $minDelta = null, $echo = true)
{
    return hl::tic($label, $minDelta, debug_backtrace(), $echo);
}

/** echo debug_backtrace in table */
function bt($debug_backtrace = null, $echo = true)
{
    $debug_backtrace = $debug_backtrace ? : debug_backtrace();

    $ehl = [$debug_backtrace];
    array_unshift($ehl, '--e');
    hl::say($ehl, debug_backtrace());

    if ($echo) {
        echo '
		<table border="1">
		<caption  style="border: 4px ridge">hl debug backtrace<caption>
		<tr>
			<th>call</th>
			<th>place</th>
		</tr>
		';
        //unset($debug_backtrace);
        foreach ($debug_backtrace as $i => $data) {
            echo '
		<tr>
			<td>' . getDataVal($data, 'class') . getDataVal($data, 'type') . getDataVal($data, 'function') . '</td>
			<td>' . getDataVal($data, 'file') . ':' . getDataVal($data, 'line') . '</td>
		</tr>
		';
        }
        echo '
		</table>
		';
    }

}

/**
 * @param array $data
 * @param $key
 * @return string
 */
function getDataVal(array $data, $key)
{
    return array_key_exists($key, $data) ? $data[$key] : '';
}

/** @param $array */
function a($array)
{
    echo hl::showArray($array);
}


/**
 * @param $var
 * @return int|mixed
 *
 * Возвращает размер памяти, занимаемый переменной
 */
function sov($var) {
    return hl::sizeOfVar($var);
}