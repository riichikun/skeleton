<?php

spl_autoload_register(function ($class) {
    include __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
});