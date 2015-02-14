<?php
/** @var \rock\template\Template $this */
?>
subfoo
<?=$this->getPlaceholder('$parent.name')?> <?=$this->lastname?>
<?=$this->getChunk('subsubfoo.php')?>
<?=json_encode($this->getAllPlaceholders()). "\n"?>
<?=json_encode($this->{'$parent'}). "\n"?>
<?=json_encode($this->{'$root'}). "\n\n"?>
<?php
// remove all $parent scope
$this->removePlaceholder('$parent');
?>
<?=json_encode($this->getAllPlaceholders()). "\n"?>
<?=json_encode($this->{'$parent'}). "\n"?>
<?=json_encode($this->{'$root'}). "\n\n"?>