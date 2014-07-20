<?php
/** @var \rock\template\Template $this */
?>
<h3><?=$this->name?></h3>
<?=$this->email?>

<?=$this->getPlaceholder('about', false)?>
<?=$this->currentItem !== $this->countItems ? '<hr>' : ''?>
