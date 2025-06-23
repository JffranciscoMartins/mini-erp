<?php

namespace MiniERP\Domain\ValueObjects;

use MiniERP\Domain\Exceptions\InvalidCepException;

class Cep
{
    private string $cep;

    public function __construct(string $cep)
    {
        $cleaned = preg_replace('/[^0-9]/', '', $cep);
        
        if (strlen($cleaned) !== 8) {
            throw new InvalidCepException(
                "CEP must contain exactly 8 digits"
            );
        }

        $this->cep = $cleaned;
    }

    public function getFormatted(): string
    {
        return substr($this->cep, 0, 5) . '-' . substr($this->cep, 5);
    }

    public function getDigits(): string
    {
        return $this->cep;
    }

    public function __toString(): string
    {
        return $this->getFormatted();
    }
}