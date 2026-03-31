<?php
    spl_autoload_register(function ($classe)
    {
        $arquivo = __DIR__ . "/../model/" . strtolower($classe) . ".php";
        if (file_exists($arquivo))
        {
            require_once $arquivo;
        }
    });
?>