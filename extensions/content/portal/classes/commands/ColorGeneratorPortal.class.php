<?php

namespace Portal\Commands;

class ColorGeneratorPortal {

    private $id;

    public function setId($id) {
        $this->id = $id;
    }

    public function generateCss() {
        if (!isset($this->id)) {
            throw new \Exception("Id isn't set!");
        }
        $obj = \steam_factory::get_object($GLOBALS["STEAM"]->get_id(), $this->id);
        if (!($obj instanceof \steam_object)) {
            throw new \Exception("current steam object isn't valid!");
        }
        $component_fontcolor = $obj->get_attribute("bid:portal:component_fontcolor");
        $component_bgcolor = $obj->get_attribute("bid:portal:component_bgcolor");
        $headline_fontcolor = $obj->get_attribute("bid:portal:headline_fontcolor");
        $headline_bgcolor = $obj->get_attribute("bid:portal:headline_bgcolor");
        $content_fontcolor = $obj->get_attribute("bid:portal:content_fontcolor");
        $description_fontcolor = $obj->get_attribute("bid:portal:description_fontcolor");
        $content_bgcolor = $obj->get_attribute("bid:portal:content_bgcolor");
        $link_fontcolor = $obj->get_attribute("bid:portal:link_fontcolor");
        $bgcolor = $obj->get_attribute("bid:portal:bgcolor");

        $cp_font_css = '';
        if ($component_fontcolor !== 0) {
            $cp_font_css = '.portlet h1 {
                            color:' . $component_fontcolor . '
                            }';
        }
        $ht_font_css = '';
        if ($headline_fontcolor !== 0) {
            $ht_font_css = '.portlet h2{
                            color:' . $headline_fontcolor . '
                            }
                            ';
        }
        $ct_font_css = '';
        if ($content_fontcolor !== 0) {
            $ct_font_css = '.portlet div{
                            color:' . $content_fontcolor . '
                            }
                            ';
        }
        $dt_font_css = '';
        if ($description_fontcolor !== 0) {
            $dt_font_css = '.portlet .description{
                            color:' . $description_fontcolor . '
                            }
                            ';
        }
        $link_font_css = '';
        if ($link_fontcolor !== 0) {
            $link_font_css = '.portlet a{
                            color:' . $link_fontcolor . '
                            }
                            ';
        }
        //Die Farben liegen in Hexcode vor, oder sind 0 (Default)
        //top:0.58 0.34 0.60
        //mean:0.58 0.46 0.42
        //buttom: 0.58 0.50 0.42
        $cp_bg_css = '';

        if ($component_bgcolor !== 0) {

            $hsl = self::color_hex2hsl($component_bgcolor);

            $hsl0 = intval($hsl[0] * 360);
             $cp_bg_css = '.portlet h1{
              background: hsl(' . $hsl0 . ',' . $hsl[1] * 100 . '%,' . $hsl[2] * 100 . '%);
              background: -webkit-gradient(linear, left top, left bottom, from(hsl(' . $hsl0 . ',' . max($hsl[1], 0) * 100 . '%,' . max($hsl[2]-0.05, 0) * 100 . '%)),
              to(hsl(' . $hsl0 . ',' . min($hsl[1]+0.05, 1) * 100 . '%,' . min($hsl[2], 1) * 100 . '%)));
              background: -moz-linear-gradient(top,hsl(' . $hsl0 . ',' . max($hsl[1], 0) * 100 . '%,' . max($hsl[2], 0) * 100 . '%),
              hsl(' . intval($hsl[0] * 360) . ',' . min($hsl[1]+0.05, 1) * 100 . '%,' . min($hsl[2]+0.05, 1) * 100 . '%));
                  border: 0px solid #356FA1;

              }';

        }
        $headline_bgcolor_css = '';
        if ( $headline_bgcolor !== 0) {

            $hsl = self::color_hex2hsl($headline_bgcolor);

            $hsl0 = intval($hsl[0] * 360);
             $headline_bgcolor_css = '.portal h2.subheadline{
              background: hsl(' . $hsl0 . ',' . $hsl[1] * 100 . '%,' . $hsl[2] * 100 . '%);
              background: -webkit-gradient(linear, left top, left bottom, from(hsl(' . $hsl0 . ',' . max($hsl[1], 0) * 100 . '%,' . max($hsl[2]-0.05, 0) * 100 . '%)),
              to(hsl(' . $hsl0 . ',' . min($hsl[1]+0.05, 1) * 100 . '%,' . min($hsl[2], 1) * 100 . '%)));
              background: -moz-linear-gradient(top,hsl(' . $hsl0 . ',' . max($hsl[1], 0) * 100 . '%,' . max($hsl[2], 0) * 100 . '%),
              hsl(' . intval($hsl[0] * 360) . ',' . min($hsl[1]+0.05, 1) * 100 . '%,' . min($hsl[2]+0.05, 1) * 100 . '%));
              }';
             $headline_bgcolor_css .= 'th{
              background: hsl(' . $hsl0 . ',' . $hsl[1] * 100 . '%,' . $hsl[2] * 100 . '%);
              background: -webkit-gradient(linear, left top, left bottom, from(hsl(' . $hsl0 . ',' . max($hsl[1], 0) * 100 . '%,' . max($hsl[2]-0.05, 0) * 100 . '%)),
              to(hsl(' . $hsl0 . ',' . min($hsl[1]+0.05, 1) * 100 . '%,' . min($hsl[2], 1) * 100 . '%)));
              background: -moz-linear-gradient(top,hsl(' . $hsl0 . ',' . max($hsl[1], 0) * 100 . '%,' . max($hsl[2], 0) * 100 . '%),
              hsl(' . intval($hsl[0] * 360) . ',' . min($hsl[1]+0.05, 1) * 100 . '%,' . min($hsl[2]+0.05, 1) * 100 . '%));
              }';
        }
         $content_bgcolor_css = '';
        if ( $content_bgcolor !== 0) {

            $hsl = self::color_hex2hsl($content_bgcolor);

            $hsl0 = intval($hsl[0] * 360);
             $content_bgcolor_css = '.portal .portlet .entry {
              background: hsl(' . $hsl0 . ',' . $hsl[1] * 100 . '%,' . $hsl[2] * 100 . '%);
              background: -webkit-gradient(linear, left top, left bottom, from(hsl(' . $hsl0 . ',' . max($hsl[1], 0) * 100 . '%,' . max($hsl[2]-0.05, 0) * 100 . '%)),
              to(hsl(' . $hsl0 . ',' . min($hsl[1]+0.05, 1) * 100 . '%,' . min($hsl[2], 1) * 100 . '%)));
              background: -moz-linear-gradient(top,hsl(' . $hsl0 . ',' . max($hsl[1], 0) * 100 . '%,' . max($hsl[2], 0) * 100 . '%),
              hsl(' . intval($hsl[0] * 360) . ',' . min($hsl[1]+0.05, 1) * 100 . '%,' . min($hsl[2]+0.05, 1) * 100 . '%));
              margin-bottom:0px;
              }';
             $content_bgcolor_css .= '#sortable-topics{
              background: hsl(' . $hsl0 . ',' . $hsl[1] * 100 . '%,' . $hsl[2] * 100 . '%);
              background: -webkit-gradient(linear, left top, left bottom, from(hsl(' . $hsl0 . ',' . max($hsl[1], 0) * 100 . '%,' . max($hsl[2]-0.05, 0) * 100 . '%)),
              to(hsl(' . $hsl0 . ',' . min($hsl[1]+0.05, 1) * 100 . '%,' . min($hsl[2], 1) * 100 . '%)));
              background: -moz-linear-gradient(top,hsl(' . $hsl0 . ',' . max($hsl[1], 0) * 100 . '%,' . max($hsl[2], 0) * 100 . '%),
              hsl(' . intval($hsl[0] * 360) . ',' . min($hsl[1]+0.05, 1) * 100 . '%,' . min($hsl[2]+0.05, 1) * 100 . '%));
              }';
             $content_bgcolor_css .= '.message{
              background: hsl(' . $hsl0 . ',' . $hsl[1] * 100 . '%,' . $hsl[2] * 100 . '%);
              background: -webkit-gradient(linear, left top, left bottom, from(hsl(' . $hsl0 . ',' . max($hsl[1], 0) * 100 . '%,' . max($hsl[2]-0.05, 0) * 100 . '%)),
              to(hsl(' . $hsl0 . ',' . min($hsl[1]+0.05, 1) * 100 . '%,' . min($hsl[2], 1) * 100 . '%)));
              background: -moz-linear-gradient(top,hsl(' . $hsl0 . ',' . max($hsl[1], 0) * 100 . '%,' . max($hsl[2], 0) * 100 . '%),
              hsl(' . intval($hsl[0] * 360) . ',' . min($hsl[1]+0.05, 1) * 100 . '%,' . min($hsl[2]+0.05, 1) * 100 . '%));
              }';
              $content_bgcolor_css .= ' .podcast {

              background: hsl(' . $hsl0 . ',' . $hsl[1] * 100 . '%,' . $hsl[2] * 100 . '%);
              background: -webkit-gradient(linear, left top, left bottom, from(hsl(' . $hsl0 . ',' . max($hsl[1], 0) * 100 . '%,' . max($hsl[2]-0.05, 0) * 100 . '%)),
              to(hsl(' . $hsl0 . ',' . min($hsl[1]+0.05, 1) * 100 . '%,' . min($hsl[2], 1) * 100 . '%)));
              background: -moz-linear-gradient(top,hsl(' . $hsl0 . ',' . max($hsl[1], 0) * 100 . '%,' . max($hsl[2], 0) * 100 . '%),
              hsl(' . intval($hsl[0] * 360) . ',' . min($hsl[1]+0.05, 1) * 100 . '%,' . min($hsl[2]+0.05, 1) * 100 . '%));
                  border-top: 0px solid #CDCDCD;
              }';
              $content_bgcolor_css .= '.portlet h3{border-top: 0px solid #CDCDCD;}';
              //$content_bgcolor_css .= '.portlet h3{border-top: 0px solid #CDCDCD;}.bookmark{height:28px;}';


              }
         $bgcolor_css = '';
        if ( $bgcolor !== 0) {

            $hsl = self::color_hex2hsl($bgcolor);

            $hsl0 = intval($hsl[0] * 360);
             $bgcolor_css = '.portal{
              background: hsl(' . $hsl0 . ',' . $hsl[1] * 100 . '%,' . $hsl[2] * 100 . '%);
              background: -webkit-gradient(linear, left top, left bottom, from(hsl(' . $hsl0 . ',' . max($hsl[1], 0) * 100 . '%,' . max($hsl[2]-0.05, 0) * 100 . '%)),
              to(hsl(' . $hsl0 . ',' . min($hsl[1]+0.05, 1) * 100 . '%,' . min($hsl[2], 1) * 100 . '%)));
              background: -moz-linear-gradient(top,hsl(' . $hsl0 . ',' . max($hsl[1], 0) * 100 . '%,' . max($hsl[2], 0) * 100 . '%),
              hsl(' . intval($hsl[0] * 360) . ',' . min($hsl[1]+0.05, 1) * 100 . '%,' . min($hsl[2]+0.05, 1) * 100 . '%));
              }';
        }

        return $cp_font_css . $ht_font_css . $ct_font_css . $dt_font_css . $link_font_css . $cp_bg_css. $headline_bgcolor_css . $content_bgcolor_css .$bgcolor_css;

    }

    private static function color_hex2hsl($hex) {
        $sign = substr($hex, 0, 1);
        if ($sign == "#") {
            $hex = substr($hex, 1);
        }
        $rgb = array();
        $rgb = self::color_hex2rgb($hex);
        foreach ($rgb as $i => $ele) {
            $rgb[$i] = ($ele) / 255;
        }
        $hsl = array();
        $hsl = self::color_rgb2hsl($rgb);

        foreach ($hsl as $i => $h) {
            $hsl[$i] = number_format($h, 2, '.', '');
        }
        return $hsl;
    }

    private static function color_hex2rgb($hex) {
        $color = array();
        $color[0] = hexdec(substr($hex, 0, 2));
        $color[1] = hexdec(substr($hex, 2, 2));
        $color[2] = hexdec(substr($hex, 4, 2));
        return $color;
    }

    private static function color_rgb2hsl($rgb) {
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
