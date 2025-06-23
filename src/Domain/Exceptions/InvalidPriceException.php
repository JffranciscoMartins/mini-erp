<?php

namespace MiniERP\Domain\Exceptions;

class InvalidPriceException extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}