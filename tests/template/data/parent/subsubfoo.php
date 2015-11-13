<?php
/** @var \rock\template\Template $this */
$template = $this;
?>


subsubfoo
<?=$template['$parent.$parent.name']. "\n"?>
<?=$template['name'] . "\n"?>
<?=$this->getPlaceholder('$root.name')?> <?=$this->getPlaceholder(['$parent', 'lastname']). "\n"?>
<?=json_encode($this->getPlaceholder('$root')). "\n"?>
<?=json_encode($template['$parent']). "\n\n"?>
<?php
// add current scope
$this->addPlaceholder('age', 25);
?>
<?=json_encode($this->getAllPlaceholders()). "\n"?>
<?=json_encode($template['$parent']). "\n"?>
<?=json_encode($template['$root']). "\n\n"?>
<?php
// add $root scope
$this->addPlaceholder('$root.nickname', 'Romeo');
$template['$parent.$parent.height'] = 175;
// exception
$this->addPlaceholder('$parent.$parent.$parent.weight', 80);
?>
<?=json_encode($this->getAllPlaceholders()). "\n"?>
<?=json_encode($template['$parent']). "\n"?>
<?=json_encode($template['$root']). "\n\n"?>
<?php
// remove $root scope
unset($template['$parent.$parent.height']);
// exception
unset($template['$parent.$parent.$parent.height']);
// remove $root scope
$this->removePlaceholder('$root.nickname');
// add $parent scope
$this->addPlaceholder('$parent.nickname', 'Romeo');
?>
<?=json_encode($this->getAllPlaceholders()). "\n"?>
<?=json_encode($template['$parent']). "\n"?>
<?=json_encode($template['$root']). "\n\n"?>
<?php
// remove multi $parent scope
$this->removeMultiPlaceholders(['$parent.nickname']);
// exception
$this->removeMultiPlaceholders(['$parent.$parent.$parent.nickname']);
?>
<?=json_encode($this->getAllPlaceholders()). "\n"?>
<?=json_encode($template['$parent']). "\n"?>
<?=json_encode($template['$root']). "\n\n"?>
<?php
// remove multi current scope
$this->removeMultiPlaceholders(['age']);
?>
<?=json_encode($this->getAllPlaceholders()). "\n"?>
<?=json_encode($template['$parent']). "\n"?>
<?=json_encode($template['$root']). "\n\n"?>
<?php
// add multi current scope
$this->addMultiPlaceholders(['age' => 27]);
?>
<?=json_encode($this->getAllPlaceholders()). "\n"?>
<?=json_encode($template['$parent']). "\n"?>
<?=json_encode($template['$root']). "\n\n"?>
<?php
// add multi $root scope
$this->addMultiPlaceholders(['$parent.$parent.height' => 170]);
$this->addMultiPlaceholders(['$root.nickname' => 'Storm']);
// exception
$this->addMultiPlaceholders(['$parent.$parent.$parent.weight' =>  80]);
// add $parent scope
$this->addMultiPlaceholders(['$parent.e-mail' =>  'site@gmail.com']);
?>
<?=json_encode($this->getAllPlaceholders()). "\n"?>
<?=json_encode($template['$parent']). "\n"?>
<?=json_encode($template['$root']). "\n\n"?>
<?php
// remove all current scope
$this->removeAllPlaceholders();
// exception
$this->removeAllPlaceholders('$parent.$parent.$parent');
?>
<?=json_encode($this->getAllPlaceholders()). "\n"?>
<?=json_encode($template['$parent']). "\n"?>
<?=json_encode($template['$root']). "\n\n"?>