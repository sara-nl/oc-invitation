<?php 
// note: including the index page with the message as 'pop-up' does not work like so: print_unescaped($this->inc('files.view.index')); 
?>

<div class="error" style="
    padding: 2em;
    margin-left: auto;
    margin-right: auto;
    width: 20%;
    border: 1px solid gray;
    margin-top: 5%;
    background-color: aqua;
    font-weight: bold;
    padding-left: 5em;">
    <?php p($l->t($_['message']));?>
</div>