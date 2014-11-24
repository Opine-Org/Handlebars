<?php

class HelperToService {
    public static function __callStatic ($name, $arguments) {
        echo $name, "\n";
    }
}

HelperToService::abc();
HelperToService::{'a@c'}();

$method = 'a@d';
HelperToService::{$method}();

$method = 'a@e';
HelperToService::$method();

call_user_func_array (['HelperToService', 'a@f'], []);

call_user_func_array ('HelperToService::qqq', []);

call_user_func_array ('HelperToService::a@g', []);