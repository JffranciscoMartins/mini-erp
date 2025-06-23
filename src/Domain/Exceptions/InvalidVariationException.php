<?php

namespace MiniERP\Domain\Exceptions;

class InvalidVariationException extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}