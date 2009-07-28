<?php
/*
 これはdatetitleというプラグインです。titleにフィルターをかけ、変換します。
 
*/ 

$this->plugin->addFilter('ex-content', "appendDates");

function appendDates($str) 
{
    return '<h1>'. date('l, M j').'</h1>'.$str;
}

