<?php
namespace HeatMap {

class Color {
    public $red;
    public $green;
    public $blue;

    public function __construct($red, $green, $blue) {
        $this->$red   = $red;
        $this->$green = $green;
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
    public static $BGR;
}

// Define ColorScales static member variables
ColorScale::$BGR = array(
    new ColorMap(0,    1.00, new Color(0,   0,   255)),
    new ColorMap(1.00, 2.00, new Color(0,   85,  255)),
    new ColorMap(2.00, 3.00, new Color(0,   170, 255)),
    new ColorMap(3.00, 4.00, new Color(0,   255, 255)),
    new ColorMap(4.00, 5.00, new Color(0,   255, 170)),
    new ColorMap(5.00, 6.00, new Color(170, 255, 0  )),
    new ColorMap(6.00, 7.00, new Color(255, 255, 0  )),
    new ColorMap(7.00, 8.00, new Color(255, 170, 0  )),
    new ColorMap(8.00, 9.00, new Color(255, 85,  0  )),
    new ColorMap(9.00, 10.0, new Color(255, 0,   0  )) 
);

} // End of HeatMap namespace

?>