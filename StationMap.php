#!/usr/bin/env php
<?php
date_default_timezone_set('Asia/Singapore');

include_once 'Station.php';
include_once 'Line.php';

class StationMap {
    // The transfer information hashmap
    private $names;

    // The stations information hashmap
    private $stations;

    // The final route suggestion 
    private $retList;

    // The minimum time consuming of the route
    private $minCost;

    public function __construct($fileName) {
        $this->names = [];
        $this->stations = [];

        $file = fopen($fileName, 'r');
        if ($file == null) {
            throw new Exception('Unable to open file.');
        }

        !feof($file) && fgets($file);
        while (!feof($file)) {
            $a = explode(',', fgets($file));
            if (count($a) != 3) {
                continue;
            }

            // Building the transfer information and the stations information
            try {
                $station = new Station($a[0], $a[1], $a[2]);
                $this->names[strtolower($a[1])][] = $a[0];
                $this->stations[$a[0]] = $station;
            } catch (Exception $e) {
                print('WARN: ' . $e->getMessage());
                continue;
            }
        }
        fclose($file);

        // Building the stations' adjacency list
        foreach ($this->names as $arr) {
            foreach ($arr as $i => $a) {
                // The previous station of the same line
                $next1 = $this->stations[$a]->getLine() . ($this->stations[$a]->getNumb() - 1);
                if (isset($this->stations[$next1])) {
                    $this->stations[$a]->addNext($next1);
                }

                // The next station of the same line
                $next2 = $this->stations[$a]->getLine() . ($this->stations[$a]->getNumb() + 1);
                if (isset($this->stations[$next2])) {
                    $this->stations[$a]->addNext($next2);
                }

                // The transferable station
                foreach ($arr as $j => $b) {
                    if ($i == $j) {
                        continue;
                    }
                    $this->stations[$a]->addNext($b);
                }
            }
        }
    }

    // Searching the fastest path from the src to the dest
    // The urban rail system is a bidirectional connected graph with the dynamic time weight,
    // so I choose the DFS for comparing each route's time comsuming
    // instead of the BFS for the simple shortest path finding
    public function search($src, $dest, $when = '') {
        $src = strtolower($src);
        $dest = strtolower($dest);
        if (!isset($this->names[$src]) || !isset($this->names[$dest])) {
            throw new Exception('Station not found');
        }

        // Using the current time if the variable when is empty
        $start = time();
        if (!empty($when) && strtotime($when) != null) {
            $start = strtotime($when);
        }

        $this->retList = [];
        $this->minCost = PHP_INT_MAX;
        // The same station name has the different station line
        foreach ($this->names[$src] as $from) {
            if ($this->stations[$from]->getOpen() > $start) {
                continue;
            }

            foreach ($this->names[$dest] as $to) {
                if ($this->stations[$to]->getOpen() > $start) {
                    continue;
                }

                $path = [];
                // Recording the stations which has been taken
                $path[$from] = 1;
                $cost = 0;
                $this->DFS($from, $to, $start, $path, $cost);
            }
        }

        return $this->retList;
    }

    private function DFS($from, $to, $start, $path, $cost) {
        if ($this->stations[$from]->getCode() == $this->stations[$to]->getCode()) {
            if ($this->minCost > $cost) {
                $this->retList = array_keys($path);
                $this->minCost = $cost;
            }
            return;
        }

        foreach ($this->stations[$from]->getNext() as $next) {
            // Avoiding the path loop and confirming the station is open
            if (isset($path[$next]) || $this->stations[$next]->getOpen() > $start) {
                continue;
            }

            // Calculating the single time consuming
            $oneCost = $this->getCost($this->stations[$from]->getLine(), $this->stations[$next]->getLine(), $start + $cost);
            // The station don't operate
            if ($oneCost == PHP_INT_MAX) {
                continue;
            }
            $oneCost *= 60;

            $path[$next] = 1;
            $cost += $oneCost;
            $this->DFS($next, $to, $start, $path, $cost);
            array_pop($path);
            $cost -= $oneCost;
        }
    }

    private function getCost($from, $to, $start) {
        if (empty($from) || empty($to) || empty($start)) {
            return PHP_INT_MAX;
        }

        $hourKey = $this->getPeriod($start);
        if ($from == $to) {
            if (isset(Line::$timetable[$hourKey][$from])) {
                return Line::$timetable[$hourKey][$from];
            } else {
                return Line::$timetable[$hourKey]['other'];
            }
        } else {
            return Line::$timetable[$hourKey]['change'];
        }
    }

    // Check if the current time is peak
    private function getPeriod($start) {
        if (empty($start)) {
            return 'other';
        }

        // Get the hour of the time
        $hour = date('G');
        // Get the day of the time
        $day = date('w');

        foreach (Line::$period as $hourKey => $info) {
            if ($day >= $info['day'][0] && $day <= $info['day'][1]) {
                foreach ($info['hour'] as $interval) {
                    if ($hour >= $interval[0] && $hour <= $interval[1]) {
                        return $hourKey;
                    }
                }
            }
        }

        return 'other';
    }
}

// The example test of the code
$stationMap = new StationMap('StationMap.csv');
$ret = $stationMap->search('Holland Village', 'Bugis');
print_r($ret);
