<?php
class Station {
    // The line number
    private $line;

    // The station's index of the line
    private $numb;

    // The whole station's Code
    private $code;

    // The station's name
    private $name;

    // The timestamp of the station opening
    private $open;

    // The stations which can this station go to or transfer to
    private $next;

    public function __construct($code, $name, $open) {
        if (empty($code) || empty($name) || empty($open)) {
            throw new Exception('Input Error.');
        }

        $this->line = substr($code, 0, 2);
        $this->numb = substr($code, 2);
        $this->code = $code;
        $this->name = $name;
        $this->open = strtotime($open);
        $this->next = [];
    }

    public function getLine() {
        return $this->line;
    }

    public function getNumb() {
        return $this->numb;
    }

    public function getCode() {
        return $this->code;
    }

    public function getName() {
        return $this->name;
    }

    public function getOpen() {
        return $this->open;
    }

    public function getNext() {
        return $this->next;
    }

    public function addNext($station) {
        if (!empty($station)) {
            $this->next[] = $station;
        }
    }
}
