<?php
/**
 * 代码调试类
 *
 */
class Helper_Debug {

    /**
     * 输出变量的内容，通常用于调试
     *
     * @param mixed $vars 要输出的变量
     * @param string $label
     * @param boolean $return
     */
    public static function dump($vars, $label = '', $return = false) {
		$content = "<pre>\n";
		if ($label != '') {
		    $content .= "<strong>{$label} :</strong>\n";
		}
		$content .= htmlspecialchars(print_r($vars, true));
		$content .= "\n</pre>\n";

        if ($return) return $content;
        echo $content;
        return null;
    }

    /**
     * 显示应用程序执行路径，通常用于调试
     *
     * @return string
     */
    public static function dumpTrace() {
        $debug = debug_backtrace();
        $lines = '';
        $index = 0;
        for ($i = 0; $i < count($debug); $i ++) {
            if ($i == 0) continue;

            $file = $debug[$i];
            if (!isset($file['file'])) $file['file'] = 'eval';
            if (! isset($file['line'])) $file['line'] = null;

            $line = "#{$index} {$file['file']}({$file['line']}): ";
            if (isset($file['class'])) $line .= "{$file['class']}{$file['type']}";
            $line .= "{$file['function']}(";
            if (isset($file['args']) && count($file['args'])) {
                foreach ($file['args'] as $arg) {
                    $line .= gettype($arg) . ', ';
                }
                $line = substr($line, 0, - 2);
            }
            $line .= ')';
            $lines .= $line . "\n";
            $index ++;
        } // for

        $lines .= "#{$index} {main}\n";
        if (ini_get('html_errors')) {
            echo nl2br(str_replace(' ', '&nbsp;', $lines));
        } else {
            echo $lines;
        }
    }
}
