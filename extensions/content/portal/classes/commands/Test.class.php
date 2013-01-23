<?php

namespace Portal\Commands;

class Test extends \AbstractCommand implements \IFrameCommand {

    public function validateData(\IRequestObject $requestObject) {
        return true;
    }

    public function processData(\IRequestObject $requestObject) {
        $this->params = $requestObject->getParams();
        isset($this->params[0]) ? $this->id = $this->params[0] : "";

        $value = "51 51 51";
        $array = array();
        $array[0] = $array[1] = $array[2] = 51/255  ;
        var_dump(self::_color_rgb2hsl($array));die;
    }

    public function frameResponse(\FrameResponseObject $frameResponseObject) {



        return $frameResponseObject;
    }

    static  function color_rgb2hsl($rgb) {
        $r = $rgb[0];
        $g = $rgb[1];
        $b = $rgb[2];
        $min = min($r, min($g, $b));
        $max = max($r, max($g, $b));
        $delta = $max - $min;
        $l = ($min + $max) / 2;
        $s = 0;
        if ($l > 0 && $l < 1) {
            $s = $delta / ($l < 0.5 ? (2 * $l) : (2 - 2 * $l));
        }
        $h = 0;
        if ($delta > 0) {
            if ($max == $r && $max != $g)
                $h += ($g - $b) / $delta;
            if ($max == $g && $max != $b)
                $h += (2 + ($b - $r) / $delta);
            if ($max == $b && $max != $r)
                $h += (4 + ($r - $g) / $delta);
            $h /= 6;
        }
        return array($h, $s, $l);
    }

}

?>