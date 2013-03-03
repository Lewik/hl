<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Lewik (lllewik at gmail dot com)
 */

class ___hlMainClass
{ //u can rename it, use search to find

	protected static $instance;


	public $executionsRemain = 10;
	public $logFile = 'hl_log.html';
	private $top = 0;
	private $topStep = 20;
	private $z_indexMin = 10000;
	private $z_indexMax = 11000;
	private $options = array();
	private $data;


	private function __construct()
	{
		if (file_exists($this->logFile)) {
			unlink($this->logFile);
		}
		/* ... @return Singleton */
	}

	private function __clone()
	{ /* ... @return Singleton */
	}

	private function __wakeup()
	{ /* ... @return Singleton */
	}

	public static function get() {
		if ( is_null(self::$instance) ) {
			self::$instance = new ___hlMainClass ();
		}
		return self::$instance;
	}


	public function hl($data, $debug_backtrace)
	{
		$this->data = $data;
		if ($this->executionsRemain == 0) {
			return false;
		}
		$this->executionsRemain--;

		$this->setOptions();

		$d_back = $debug_backtrace[0];
		$d_backCall = $debug_backtrace[1];
		$this->top = $this->top + $this->topStep;
		$htmlTemplate = $this->getHtmlTemplate();
		$jsTemplate = '
            <script type="text/javascript">
            console.log("hl message: file(line): {file}({line}) call: {call} ({callType}). Value below");
            console.log(" { jsValue} ");
            </script>
    ';
		$fileTemplate = '
                    <html>
                        <head>
                            <title>
                                HL
                            </title>
                        </head>
                        <body>
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

		if (count($this->data) == 0) {
			$this->data[] = 'Executed';
		}

		if ($this->executionsRemain == 0) {
			$this->data[] = 'Last execution';
		}

		foreach ($this->data as $value) {
			ob_start();
			var_dump($value);
			$buffer = ob_get_contents();
			ob_end_clean();

			$valueToDisplay = htmlspecialchars($buffer, ENT_QUOTES);
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


			if ($this->options['h']) {
				echo $html;
			}
			if ($this->options['j']) {
				echo $js;
			}
			if ($this->options['f']) {
				if (!file_exists($this->logFile)) {
					$res = @file_put_contents($this->logFile, "");
					if ($res === false) {
						$this->hlError('hl can\'t create hl_log.php');
					}
				}

				@file_put_contents($this->logFile, file_get_contents($this->logFile) . $file);
			}


		}
		return true;
	}

	private function hlError($text)
	{

	}

	private function getHtmlTemplate($hlError = false)
	{
		$uid = $this->executionsRemain;
		$additionalCss = $hlError ? 'float:left;' : '';
		return '
            <style type="text/css">
                .mblhlMain{
                    background-color: #d3d3d3;
                    border: dashed 2px grey;
                    font-size: 12px;
                    font-family: consolas;
                    position: fixed;
                    z-index: ' . $this->z_indexMax . ';
                    left: 0;
                    top: 0;
                    max-height: 800px;
                    max-width: 1000px;
                    min-height: 20px;
                    min-width: 149px;
                    overflow: auto;
                    ' . $additionalCss . '
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


            <div class="mblhlMain" id="___mblHlDebugDiv_' . $uid . '" style="top:' . $this->top . 'px">
                <div class="mblhlLabel">
                    hl
                    <span onclick="___mblHlToggle_' . $uid . '()" class="mblhlMinimize">Minimize</span>
                    <span onclick="___mblHlClose_' . $uid . '()" class="mblhlClose">Close</span>
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
                        <pre style="font-family: consolas">{value}</pre>
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
                        document.getElementById("___mblHlDebugDiv_' . $uid . '").style.zIndex="' . $this->z_indexMax . '";
                    } else {
                        document.getElementById("___mblHlDebugBody_' . $uid . '").style.display = "none";
                        document.getElementById("___mblHlDebugDiv_' . $uid . '").style.zIndex="' . $this->z_indexMin . '";
                    }
                }
                ___mblHlToggle_' . ($uid + 1) . '()
            </script>
    ';
	}

	private function setOptions(){
		$this->options['h'] = TRUE;
		$this->options['j'] = TRUE;
		$this->options['f'] = TRUE;

		$optionFlag = '--';


		if (is_string($this->data[0]) and substr($this->data[0], 0, 2) == $optionFlag) {
			$selectedOptions = $this->data[0];
			unset($this->data[0]);
			$selectedOptions = str_replace($optionFlag, '', $selectedOptions);
			$selectedOptions = explode(' ', $selectedOptions);
			foreach ($this->options as $key => $enable) {
				$this->options[$key] = !!in_array($key, $selectedOptions);
			}
		}
	}

}

function hl() //u can rename it
{
	___hlMainClass::get()->hl(func_get_args(), debug_backtrace());
}

function dhl() //u can rename it
{
	___hlMainClass::get()->hl(func_get_args(), debug_backtrace());
	die();
}