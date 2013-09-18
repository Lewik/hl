<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Lewik (lllewik at gmail dot com)
 */

class hl
{ //u can rename it
    protected static $prevTimer = false;
    protected static $prevLabel = false;
    protected static $minDelta = 0;

    protected static $useAutoUpdate = 0;
    protected static $executionsRemain = 10000000000;
    protected static $logFile = 'hl_log.html';
    protected static $top = 0;
    protected static $topStep = 20;
    protected static $z_indexMin = 10000;
    protected static $z_indexMax = 11000;
    protected static $options = array();
    protected static $data;
    protected static $firstStart = true;

    public static function tic($label = null, $minDelta = null, $debug_backtrace, $echo = true)
    {
        $d_back = $debug_backtrace[0];
        $backTraceLabel = ' <small>(' . basename($d_back['file']) . ':' . $d_back['line'] . ')</small>';
        $return = null;
        if ($minDelta !== null && $minDelta != static::$minDelta) {
            static::$minDelta = $minDelta;
            if($echo){
                echo '<small>hl timer minimum delta set at</small> ' . static::$minDelta . '<small>secs</small>' . $backTraceLabel . '<br>';
            }
        }

        if (static::$prevTimer === false) {
            static::$prevTimer = microtime(true);
            static::$prevLabel = ($label === null) ? '' : (string)$label;
            if($echo){
                echo '<small>hl timer start at label</small> ' . static::$prevLabel . $backTraceLabel . '<br>';
            }
            $return = 'start';
        } else {
            $prevTime = static::$prevTimer;
            $prevLabel = static::$prevLabel;
            $currentTime = microtime(true);
            $currentLabel = ($label === null) ? '' : (string)$label;
            $currentLabel = $currentLabel . $backTraceLabel;

            $realDelta = $currentTime - $prevTime;
            $delta = round($realDelta, 4);
            if ($echo && $realDelta >= static::$minDelta) {
                echo '<span style="white-space: nowrap">' . $prevLabel . ' &rarr; <b>' . $delta . '</b><small>secs</small> &rarr; ' . $currentLabel . '</span><br>';
            }
            static::$prevTimer = $currentTime;
            static::$prevLabel = $currentLabel;
            $return = $realDelta;
        }

        return $return;
    }

    protected static function initFile()
    {
        if (file_exists(static::$logFile)) {
            unlink(static::$logFile);
        }
    }

    public static function say($data, $debug_backtrace)
    {
        if (static::$firstStart) {
            static::initFile();
        }
        static::$data = $data;
        if (static::$executionsRemain <= 0) {
            return false;
        }
        static::$executionsRemain--;


        static::setOptions();

        $d_back = $debug_backtrace[0];
        $d_backCall = $debug_backtrace[1];
        static::$top = static::$top + static::$topStep;
        $htmlTemplate = static::getHtmlTemplate(!static::$options['f']);
        $jsTemplate = '
            <script type="text/javascript">
            console.log("hl message: file(line): {file}({line}) call: {call} ({callType}). Value below");
            console.log(" { jsValue} ");
            </script>
    ';
        if (static::$useAutoUpdate && static::$firstStart) {
            $autoUpdate = '
				<span style="position: fixed; right: 0; background-color: gray;z-index:11100; border: 2px solid black;padding: 3px; font-weight: bold; color: white">
					<span id="timer"></span>
					<input type="checkbox" id="autoUpdateDisable"/>
				</span>
				<script type="text/javascript">
						var timerI = 2;
						function reset() {
							if(!document.getElementById("autoUpdateDisable").checked){
								timerI = timerI - 1;
							}
						    if (timerI >= 0) document.getElementById("timer").innerText = timerI;
						    if (timerI == 0) window.location.replace(window.location);
						}
						setInterval("reset();", 1000);
				</script>
			';
        } else {
            $autoUpdate = '';
        }

        $fileTemplate = '
                    <html>
                        <head>
                            <title>
                                HL
                            </title>
                        </head>
                        <body>
							' . $autoUpdate . '

							{html}
                        </body>
                    </html>
    ';

        $tags = array(
            '{file}',
            '{line}',
            '{call}',
            '{callType}',
            '{value}',
            '{jsValue}',
        );

        if (count(static::$data) == 0) {
            static::$data[] = 'Executed';
        }

        if (static::$executionsRemain == 0) {
            static::$data[] = 'Last execution';
        }

        foreach (static::$data as $value) {
            ob_start();
            var_dump($value);
            $buffer = ob_get_contents();
            ob_end_clean();


            $valueToDisplay = static::isNoPre() ? $buffer : htmlspecialchars($buffer, ENT_QUOTES);
            $valueToConsole = str_replace('"', '\"', $buffer);

            if (isset($d_backCall['type'])) {
                switch ($d_backCall['type']) {
                    case '->':
                        $callType = 'method';
                        break;
                    case '::':
                        $callType = 'static';
                        break;
                    case '':
                        $callType = 'function';
                        break;
                    default :
                        $callType = ' (' . $d_backCall['type'] . ') ';
                }
            } else {
                $callType = ' (undefined call type) ';
                $d_backCall['type'] = '_undefined_call_type_';
            }

            if (!isset($d_backCall['class'])) {
                $d_backCall['class'] = '_undefined_class_';
            }

            $replace = array(
                $d_back['file'],
                $d_back['line'],
                $d_backCall['function'] ? $d_backCall['class'] . $d_backCall['type'] . $d_backCall['function'] : '',
                $d_backCall['function'] ? $callType : 'global',
                $valueToDisplay,
                $valueToConsole,
            );

            $html = str_replace($tags, $replace, $htmlTemplate);
            $js = str_replace($tags, $replace, $jsTemplate);
            $file = str_replace('{html}', $html, $fileTemplate);


            if (static::$options['h']) {
                echo $html;
            }
            if (static::$options['j']) {
                echo $js;
            }
            if (static::$options['f']) {
                if (!file_exists(static::$logFile)) {
                    $res = @file_put_contents(static::$logFile, "");
                    if ($res === false) {
                        static::hlError('hl can\'t create hl_log.php');
                    }
                }

                @file_put_contents(static::$logFile, file_get_contents(static::$logFile) . $file);
            }


        }
        static::$firstStart = false;

        return true;
    }

    protected static function hlError($text)
    {

    }

    protected static function getHtmlTemplate($isFixed = true)
    {
        $uid = static::$executionsRemain;
        if ($isFixed) {
            $fixedCss = '
					position: fixed;
                    z-index: ' . static::$z_indexMax . ';
                    left: 0;
                    top: 0;
        ';
        } else {
            $fixedCss = '';
        }

        return '
            <style type="text/css">
                .mblhlMain{
                    background-color: #d3d3d3;
                    border: dashed 2px grey;
                    font-size: 12px;
                    font-family: consolas;
                    ' . $fixedCss . '
                    max-height: 800px;
                    max-width: 1000px;
                    min-height: 20px;
                    min-width: 149px;
                    overflow: auto;
                }


                .mblhlLabel{
                    background-color: red;
                    padding-left: 5px;
                    white-space: nowrap;
                }
                .mblhlHr{
                    border: dashed 2px red;
                }
                .mblhlBodyHead{

                }
                .mblhlContent{
                    padding: 20px;
                }
                .mblhlMinimize,.mblhlClose{
                    cursor: pointer;
                    border-bottom: dashed black 1px;
                }
            </style>


            <div class="mblhlMain" id="___mblHlDebugDiv_' . $uid . '" style="top:' . static::$top . 'px">
                <div class="mblhlLabel">
                    hl
                    <span onclick="___mblHlToggle_' . $uid . '()" class="mblhlMinimize">Minimize</span>
                    <span onclick="___mblHlClose_' . $uid . '()" class="mblhlClose">Close</span>
                    <span class="mblhlTime">' . mktime() . '</span>
                </div>
                <div id="___mblHlDebugBody_' . $uid . '">
                    <div class="mblhlBodyHead">
                        <span class="mblhlInfo" title="file:line">
                            {file}:{line}
                        </span>

                        <span class="mblhlInfo" title="call (callType)">
                            ({callType}) {call}
                        </span>
                    </div>

                    <hr class="mblhlHr"/>

                    <div class="mblhlContent">
                        ' . (static::isNoPre() ? '{value}' : '<pre style="font-family: consolas">{value}</pre>') . '
                    </div>
                </div>
            </div>

            <script type="text/javascript">
                function ___mblHlClose_' . $uid . '(){
                    document.getElementById("___mblHlDebugDiv_' . $uid . '").style.display="none";
                }
                function ___mblHlToggle_' . $uid . '(){
                    if(document.getElementById("___mblHlDebugBody_' . $uid . '").style.display){
                        document.getElementById("___mblHlDebugBody_' . $uid . '").style.display = "";
                        document.getElementById("___mblHlDebugDiv_' . $uid . '").style.zIndex="' . static::$z_indexMax . '";
                    } else {
                        document.getElementById("___mblHlDebugBody_' . $uid . '").style.display = "none";
                        document.getElementById("___mblHlDebugDiv_' . $uid . '").style.zIndex="' . static::$z_indexMin . '";
                    }
                }
            </script>
    ';
    }


    /**
     * @param $array
     * @return string
    array(
    'data' = $array;
    'showNumericFields' = false;
    'maxValueDumpLength' = 30;
    )
     */
    public static function showArray($array)
    {

        $messages = array();
        if (array_key_exists('data', $array)) {
            $data = $array['data'];
        } else {
            $messages[] = 'data key not found';
            $data = $array;
        }
        if (is_object(current($data))) {
            $newData = array();
            foreach ($data as $i => $object) {
                $subData = array();
                foreach ($object as $field => $value) {
                    $subData[$field] = $value;
                }
                $newData[$i] = $subData;
            }
            $data = $newData;
        }
        $showNumericFields = array_key_exists('showNumericFields', $array) && $array['showNumericFields'];
        $maxValueDumpLength = array_key_exists('maxValueDumpLength', $array) ? $array['maxValueDumpLength'] : 30;


        $tableHeader = '<tr style="font-size: 10px; font-family: Verdana;">';
        $tableHeader .= '<th>ROWKEY</th>';

        foreach (array_keys(current($data)) as $fieldName) {
            if (!$showNumericFields && is_numeric($fieldName)) {
                continue;
            }
            $tableHeader .= '<th>' . $fieldName . '</th>';
        }
        $tableHeader .= '</tr>';

        $dataRowsHtml = '';
        foreach ($data as $rowKey => $row) {
            $rowHtml = '<tr>';

            ob_start();
            var_dump($rowKey);
            $valueDump = ob_get_contents();
            ob_end_clean();
            $valueHtml = '<th style="font-size: 10px; font-family: Verdana;">';
            $valueHtml .= (strlen($valueDump) > $maxValueDumpLength) ? substr(
                    $valueDump,
                    0,
                    $maxValueDumpLength
                ) . '...' : $valueDump;
            $valueHtml .= '</th>';

            $rowHtml .= $valueHtml;

            foreach ($row as $field => $value) {
                if (!$showNumericFields && is_numeric($field)) {
                    continue;
                }
                ob_start();
                var_dump($value);
                $valueDump = ob_get_contents();
                ob_end_clean();
                $valueHtml = '<td style="font-size: 10px; font-family: Verdana;">';
                $valueHtml .= (strlen($valueDump) > $maxValueDumpLength) ? substr(
                        $valueDump,
                        0,
                        $maxValueDumpLength
                    ) . '...' : $valueDump;
                $valueHtml .= '</td>';

                $rowHtml .= $valueHtml;
            }
            $rowHtml .= '</tr>';
            $dataRowsHtml .= $rowHtml;
        }

        $messages = implode('<br>', $messages);
        $html = $messages ? '<br>' . $messages . '<br>' : '';
        $html .= '<table border="1" style="margin: 10px">';
        $html .= $tableHeader;
        $html .= $dataRowsHtml;
        $html .= '</table>';

        return $html;
    }

    protected static function setOptions()
    {
        static::$options['h'] = true;
        static::$options['j'] = true;
        static::$options['f'] = true;

        $optionFlag = '--';


        if (is_string(static::$data[0]) and substr(static::$data[0], 0, 2) == $optionFlag) {
            $selectedOptions = static::$data[0];
            unset(static::$data[0]);
            $selectedOptions = str_replace($optionFlag, '', $selectedOptions);
            $selectedOptions = explode(' ', $selectedOptions);
            foreach (static::$options as $key => $enable) {
                static::$options[$key] = !!in_array($key, $selectedOptions);
            }
        }
    }

    protected static function isNoPre()
    {
        return ini_get('xdebug.profiler_enable') !== '';
    }

    public static function setExecutionsRemain($executionsRemain)
    {
        static::$executionsRemain = $executionsRemain;
    }


}
