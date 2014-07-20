<?php
/** @var \rock\template\Template $this */
?>
<div role="navigation" class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button data-target=".navbar-collapse" data-toggle="collapse" class="navbar-toggle" type="button">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="/" class="navbar-brand">DEMO</a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li <?=$this->getPlaceholder('active.rock', false, true) ? 'class="active"' : ''?>><a href="/">Rock engine</a></li>
                <li <?=$this->getPlaceholder('active.php', false, true) ? 'class="active"' : ''?>><a href="/php.php">PHP engine</a></li>
            </ul>
        </div>
    </div>
</div>