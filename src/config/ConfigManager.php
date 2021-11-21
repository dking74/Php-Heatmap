<?php
namespace HeatMap {

class ConfigManager {
    public static function merge($default, $inputted) {
        return array_replace($default, $inputted);
    }
}

class HeatmapConfigManager {
    /** @var \HeatMap\HeatMap $heatmap*/ private $heatmap;

    public function __construct($heatmap_instance) {
        $this->heatmap = $heatmap_instance;
    }

    public function manage() {
        // Get the config file from the heatmap and add appropriate options
        $config = $this->heatmap->getConfig();
        if (array_key_exists("title", $config))
            $this->addTitle($config["title"], $config["titlefontsize"], $config["titleangle"]);
        
    }

    private function addTitle($title_name, $font_size, $angle) {
        // Get the border parameters necessary to draw title
        $heat_border  = $this->heatmap->getBorder();
        $upper_x      = $heat_border[0]["x"];
        $upper_y      = $heat_border[0]["y"];
        $lower_x      = $heat_border[1]["x"];
        $heat_middle  = (($lower_x - $upper_x) / 2) + $upper_x;
     
        // Get the image for the heatmap and draw the text to the middle of screen
        $image = $this->_heatmap->getImage();
        $bbox = imagettfbbox($font_size, $angle, $image->getFont(), $title_name);
        $x = $bbox[0] + $heat_middle - ($bbox[4] / 2);
        $image->drawText($title_name, $x, $upper_y - 15, $angle, $font_size);
    }
}

}

?>