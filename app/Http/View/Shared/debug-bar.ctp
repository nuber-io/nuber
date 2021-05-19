<?php

use Debug\DebugBar;

if (class_exists(DebugBar::class)) {
    echo (new DebugBar())->render();
}
