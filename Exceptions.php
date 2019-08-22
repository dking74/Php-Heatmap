<?php

namespace HeatMap {

class HeatMapException extends \Exception {
    public function __construct($message, $code = 0, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

// Classes for Bad Axis data
class EmptyAxisException extends HeatMapException {}
class InvalidDimensionsException extends HeatMapException {}

// Classes for Bad Config information
class InvalidColorscaleException extends HeatMapException {}

}

?>