<?php
/** @var \rock\template\Template $this */
$template = $this;
?>
subfoo
<?=$this->getPlaceholder('$parent.name')?> <?=$this->getPlaceholder('lastname')?>
<?=$this->getChunk('subsubfoo.php')?>
<?=json_encode($this->getAllPlaceholders()). "\n"?>
<?=json_encode($template['$parent']). "\n"?>
<?=json_encode($template['$root']). "\n\n"?>
<?php
// remove all $parent scope
$this->removePlaceholder('$parent');
?>
<?=json_encode($this->getAllPlaceholders()). "\n"?>
<?=json_encode($template['$parent']). "\n"?>
<?=json_encode($template['$root']). "\n\n"?>