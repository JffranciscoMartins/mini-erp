<?php

namespace MiniERP\Domain\Exceptions;

class CouponMinValueException extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}