<?php
namespace HeatMap;

class ColorMap {
    public $low;
    public $high;

    public $red;
    public $green;
    public $blue;

    public __construct($low, $high, $red, $green, $blue) {
        $this->low   = $low;
        $this->high  = $high;
        $this->red   = $red;
        $this->green = $green;
        $this->blue  = $blue;
    } 
}

class ColorScale {
    public static $BGR = array(
        ColorMap(0,   1.00, 0, 0, 255),
        ColorMap(1.00, 2.00, ),
        ColorMap(2.00, 3.00, ),
        ColorMap(3.00, 4.00, ),
        ColorMap(4.00, 5.00, 0, 255, 0),
        ColorMap(5.00, 6.00, ),
        ColorMap(6.00, 7.00, ),
        ColorMap(7.00, 8.00, ),
        ColorMap(8.00, 9.00, ),
        ColorMap(9.00, 10.0, 0, 0) 
    );
}

?>