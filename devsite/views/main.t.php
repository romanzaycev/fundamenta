<?php
/** @var \Romanzaycev\Tooolooop\Scope\Scope $this */
/** @var string $title */
/** @var string $text */
/** @var int $session_counter */

$this->extend("layouts/base", [
    "title" => $title,
]);
?>

Hello world! <?=$this->e($text)?>
<br />

<small>
    Session counter: <?=$this->e($session_counter)?>
</small>

<br>
<a href="/protected">Protected area</a>
