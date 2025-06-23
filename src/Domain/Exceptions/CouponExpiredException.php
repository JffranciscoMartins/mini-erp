<?php

namespace MiniERP\Domain\Exceptions;

class CouponExpiredException extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}