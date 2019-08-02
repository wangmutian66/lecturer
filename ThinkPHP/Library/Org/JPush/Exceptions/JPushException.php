<?php
namespace Org\JPush\Exceptions;

class JPushException extends \Exception {

    function __construct($message) {
        parent::__construct($message);
    }
}
