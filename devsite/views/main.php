<?php
/** @var \Romanzaycev\Tooolooop\Scope\Scope $this */
/** @var string $title */
/** @var string $text */

$this->extend("layouts/base", [
    "title" => $title,
]);
?>

Hello world! <?=$this->e($text)?>
