<?php
use rock\template\Template;
/** @var Template $this */
?>
Test <?=$this->getChunk('@rockunit.views\subchunk')?>

<?=$this->getPlaceholder('text')?>

[[+escape]]
<?=$this->getPlaceholder('hi')?>, <?=$this->getPlaceholder('world')?>!!!
<?=$this->getPlaceholder(['$root', 'foo', 'bar'])?>

<?=$this->{'$root.baz.bar'}?>