<?php
require_once '../php/Emojificator.php';

$x = new Emojificator('../data');

echo $x->emoji2text($_POST['text']);
