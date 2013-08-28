<?php
namespace Application;

class Debug
{
    public static function var_dump($expression, $maxIndentations = 1, $return = false)
    {
        // Need to buffer output since var_dump doesn't allow output capturing. This might screw up
        // other code using output buffering.
        ob_start();
        var_dump($expression);
        $s = ob_get_clean();

        $lines = explode("\n", $s);
        $maxDepth = $maxIndentations * 2; // 2 spaces per indentation
        $out = join("\n", self::filterLines($lines, $maxDepth));
        if ($return) {
            return $out;
        } else {
            echo $out;
        }
    }

    public static function print_r($expression, $maxIndentations = 1, $return = false)
    {
        $s = print_r($expression, true);
        $lines = explode("\n", $s);
        $maxDepth = $maxIndentations * 4; // 4 spaces per indentation
        $out = join("\n", self::filterLines($lines, $maxDepth));
        if ($return) {
            return $out;
        } else {
            echo $out;
        }
    }

    public static function filterLines($lines, $maxDepth = 4)
    {
        $outLines = array();
        foreach ($lines as $line) {
            $isEmpty = empty($line);
            $isRecursionLine = preg_match('*RECURSION*', $line);
            $isTooDeep = preg_match('/^\s{' . ($maxDepth + 1) . '}/', $line);
            if (! $isEmpty && ! $isRecursionLine && ! $isTooDeep) {
                $outLines[] = $line;
            }
        }
        return $outLines;
    }
}