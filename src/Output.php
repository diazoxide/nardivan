<?php

namespace NovemBit\nardivan;

class Output
{
    private static $foreground_colors = array(
        'black' => '0;30',
        'dark_gray' => '1;30',
        'blue' => '0;34',
        'light_blue' => '1;34',
        'green' => '0;32',
        'light_green' => '1;32',
        'cyan' => '0;36',
        'light_cyan' => '1;36',
        'red' => '0;31',
        'light_red' => '1;31',
        'purple' => '0;35',
        'light_purple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'light_gray' => '0;37',
        'white' => '1;37',
    );
    private static $background_colors = array(
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light_gray' => '47',
    );

    // Returns colored string
    public static function getColoredString($string, $foreground_color = null, $background_color = null)
    {
        $colored_string = "";

        // Check if given foreground color found
        if (isset(self::$foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . self::$foreground_colors[$foreground_color] . "m";
        }
        // Check if given background color found
        if (isset(self::$background_colors[$background_color])) {
            $colored_string .= "\033[" . self::$background_colors[$background_color] . "m";
        }

        // Add string and end coloring
        $colored_string .= $string . "\033[0m";

        return $colored_string;
    }

    /**
     * @param $text
     * @param bool $newline
     * @param null $color
     * @param null $background
     * @param bool $return
     * @return string
     */
    public static function print($text, $newline = true, $color = null, $background = null, $return = false)
    {
        $suffix = str_repeat(PHP_EOL, (int)$newline);
        $text = $text . $suffix;

        $text = self::getColoredString($text, $color, $background);

        if (!$return) {
            echo $text;
        }
        return $text;
    }


    public static function tree(
        $message,
        $step = 1,
        $type = "note",
        $arrow_left = "",
        $symbol = '==',
        $arrow_right = '>'
    ) {
        self::print($arrow_left . str_repeat($symbol, $step) . $arrow_right . " ", false, 'light_purple');
        call_user_func_array([self::class, $type], [$message, 0, 1]);
    }

    public static function modal(
        $message,
        $message_color = "green",
        $prefix = '',
        $prefix_color = 'red',
        $suffix = '',
        $suffix_color = 'red',
        $before_line = 1,
        $after_line = 1
    ) {
        self::print('', $before_line);
        if ($prefix) {
            self::print($prefix, false, $prefix_color);
        }
        self::print($message, 1, $message_color);
        if ($suffix) {
            self::print($suffix, false, $suffix_color);
        }
        self::print('', $after_line);

    }

    public static function green($message, $before_line = 1, $after_line = 0)
    {
        self::modal($message, 'green', '', '', '', '', $before_line, $after_line);
    }

    public static function blue($message, $before_line = 1, $after_line = 0)
    {
        self::modal($message, 'blue', '', '', '', '', $before_line, $after_line);
    }

    public static function red($message, $before_line = 1, $after_line = 0)
    {
        self::modal($message, 'red', '', '', '', '', $before_line, $after_line);
    }

    public static function yellow($message, $before_line = 1, $after_line = 0)
    {
        self::modal($message, 'yellow', '', '', '', '', $before_line, $after_line);
    }

    public static function note($message, $before_line = 1, $after_line = 1)
    {
        self::modal($message, 'light_gray', 'Note: ', 'light_purple', '', '', $before_line, $after_line);
    }

    public static function success($message, $before_line = 1, $after_line = 1)
    {
        self::modal($message, 'yellow', 'Success: ', 'light_green', '', '', $before_line, $after_line);
    }

    public static function warning($message, $before_line = 1, $after_line = 1)
    {
        self::modal($message, 'light_red', 'Warning: ', 'yellow', '', '', $before_line, $after_line);
    }

    public static function error($message, $before_line = 1, $after_line = 1)
    {
        self::modal($message, 'yellow', 'Error: ', 'light_red', '', '', $before_line, $after_line);
    }
}


