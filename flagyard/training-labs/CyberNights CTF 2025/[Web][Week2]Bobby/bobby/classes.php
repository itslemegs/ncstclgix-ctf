<?php
class ChessGame {
    public $position;
    
    public function __construct($position) {
        $this->position = $position;
    }

    public function __toString() {
        return file_get_contents($this->position);
    }
}

class MoveValidator {
    public $move;
    public $analyzer;
    public $chess;
    
    public function __construct($move) {
        $this->move = $move;
    }

    public function __wakeup() {
        if ($this->analyzer && $this->chess) {
            $this->analyzer->validateMove($this->chess);
        }
    }
}

class PositionAnalyzer {
    public $gameRecord;
    public $currentPosition;
    
    public function __construct($record) {
        $this->gameRecord = $record;
    }

    public function validateMove($position) {
        $this->currentPosition = $position;
    }

    public function __destruct() {
        if ($this->currentPosition) {
            echo $this->currentPosition;
        }
    }
} 