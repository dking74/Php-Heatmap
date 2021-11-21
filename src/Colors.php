<?php
namespace HeatMap {

class Color {
    public $red;
    public $green;
    public $blue;

    public function __construct($red, $green, $blue) {
        $this->red   = $red;
        $this->green = $green;
        $this->blue   = $blue;
    }
}

class ColorMap {
    public $low;
    public $high;
    public $color;

    public function __construct($low, $high, $color) {
        $this->low   = $low;
        $this->high  = $high;
        $this->color = $color;
    } 
}

class ColorScale {
    /** @var Array $RGB */ public static $RGB;
}

// Define ColorScales static member variables
ColorScale::$RGB = array(
    new ColorMap(0,     10.00, new Color(255, 0,   0  )),
    new ColorMap(10.01, 20.00, new Color(255, 85,  0  )),
    new ColorMap(20.01, 30.00, new Color(255, 170, 0  )),
    new ColorMap(30.01, 40.00, new Color(255, 255, 0  )),
    new ColorMap(40.01, 50.00, new Color(170, 255, 0  )),
    new ColorMap(50.01, 60.00, new Color(0,   255, 170)),
    new ColorMap(60.01, 70.00, new Color(0,   255, 255)),
    new ColorMap(70.01, 80.00, new Color(0,   170, 255)),
    new ColorMap(80.01, 90.00, new Color(0,   85,  255)),
    new ColorMap(90.01, 100.0, new Color(0,   0,   255)) 
);

} // End of HeatMap namespace

?>