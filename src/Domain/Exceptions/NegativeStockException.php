<?php

namespace MiniERP\Domain\Exceptions;

class NegativeStockException extends \DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}