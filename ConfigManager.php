<?php
namespace HeatMap {

class ConfigManager {
    public static function merge($default, $inputted) {
        return array_replace($default, $inputted);
    }
}

class HeatmapConfigManager {
    public function __construct($heatmap_instance) {
        $this->_heatmap = $heatmap_instance;
    }

    public function manage($config) {}
}

}

?>