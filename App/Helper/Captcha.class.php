<?php
/**
 * 生成验证码类
 *
 */
class Helper_Captcha {
	protected static $border_color = '239, 239, 239';	// Border Color (optional)

    /**
     * Show Captcha Image 
     *
     * @param unknown_type $width	
     * @param unknown_type $height	
     * @param unknown_type $tt_font			Path to TrueType Font
     * @param unknown_type $chars_number	Number of characters
     * @param unknown_type $font_size		Font Size
     * @param unknown_type $string_type		Numbers (1), Letters (2), Letters & Numbers (3)
     */
    public static function show_image($width = 88, $height = 31, $tt_font = 'arial.ttf', $chars_number = 4, $font_size = 14, $string_type = 3) {
        if(!$tt_font || !file_exists($tt_font)) exit('The path to the true type font is incorrect.');
        if($chars_number < 3) exit('The captcha code must have at least 3 characters');

        $string = self::generate_string($chars_number, $string_type);
        $im = ImageCreate($width, $height);

        /* Set a White & Transparent Background Color */
        $bg = ImageColorAllocateAlpha($im, 255, 255, 255, 127); // (PHP 4 >= 4.3.2, PHP 5)
        ImageFill($im, 0, 0, $bg);

        /* Border Color */
        if(self::$border_color) {
            list($red, $green, $blue) = explode(',', self::$border_color);

            $border = ImageColorAllocate($im, $red, $green, $blue);
            ImageRectangle($im, 0, 0, $width - 1, $height - 1, $border);
        }

        $textcolor = ImageColorAllocate($im, 191, 120, 120);
        $y = 24;
        for($i = 0; $i < $chars_number; $i++) {
            $char = $string[$i];

            $factor = 15;
            $x = ($factor * ($i + 1)) - 6;
            $angle = rand(1, 15);
            imagettftext($im, $font_size, $angle, $x, $y, $textcolor, $tt_font, $char);
        }

        $_SESSION['fan_captcha'] = md5(strtolower($string));

        /* Output the verification image */
        header("Content-type: image/png");
        ImagePNG($im);
        exit;
    }

    protected static function generate_string($chars_number, $string_type) {
        if($string_type == 1) { // letters
            $array = range('A','Z');
        } else if($string_type == 2) { // numbers
            $array = range(1,9);
        } else { // letters & numbers
            $x = ceil($chars_number / 2);
            $array_one = array_rand(array_flip(range('A','Z')), $x);

            if($x <= 2) $x = $x - 1;
            $array_two = array_rand(array_flip(range(1,9)), $chars_number - $x);
            $array = array_merge($array_one, $array_two);
        }

        $rand_keys = array_rand($array, $chars_number);
        $string = '';
        foreach($rand_keys as $key) {
            $string .= $array[$key];
        }
        return $string;
    }

}
