<?php

include 'entry.php';

function cmp($a, $b) {
    $v = $a->date <=> $b->date;
    return $v == 0
        ? $a->kbk <=> $b->kbk
        : $v;
}

function loadMass($filename) {
    $mass = array();
    $data = file($filename);
    for($i = 0; $i < count( $data ); $i++) {
        $ln = str_getcsv($data[$i], ';', '');
        array_push($mass, new VictEntry\Entry($ln[0], $ln[1], $ln[2], $ln[3]));
    }
    return $mass;
}

function filter($mass) {
    return array_filter($mass, function($k) use ($mass) {
        if ($k + 1 == count($mass)) {
            return true;
        }
        if (cmp($mass[$k], $mass[$k + 1]) == 0) {
            $mass[$k+1]->addPercent($mass[$k]->percent_sum, $mass[$k]->percent_cnt);
            return false;
        }
        return true;
    }, ARRAY_FILTER_USE_KEY);
}

function printArray($mass) {
    echo '<pre>';
    foreach ($mass as $line) {
        echo $line->getLine();
        echo '<br />';
    }
    echo '</pre>';
}

$mass = loadMass('../csv/file.csv');

usort($mass, 'cmp');
printArray($mass);

$mass = filter($mass);
printArray($mass);