<?php

use rock\template\Template;
/** @var Template $this */

echo $this->getChunk('subchunk.php'), $this->getChunk('./subchunk.php');