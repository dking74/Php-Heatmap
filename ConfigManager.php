<?php
namespace HeatMap {

class ConfigManager {
    public static function merge($default, $inputted) {
        return array_replace($default, $inputted);
    }
}

}

?>