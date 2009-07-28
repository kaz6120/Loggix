<?php
/*
 これはdatetitleというプラグインです。titleにフィルターをかけ、変換します。
 
*/ 

$this->plugin->removeFilter('h1', 'appendDate');
$this->plugin->addFilter('h1', 'appendDate2');

function appendDate2($title) 
{
    return $title . ' :: ' . date('M d, Y G:i');
}

