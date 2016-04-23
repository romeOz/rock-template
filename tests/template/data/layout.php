<?php
use rock\template\Template;
/** @var Template $this */
?>
<p>Hello <?=$this->getPlaceholder('text')?></p>
<?=$this->getChunk('@rockunit.views\chunk', ['hi' => 'Hi', 'world' => $this->getPlaceholder('text')])?>