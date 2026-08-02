<?php

namespace App\Contracts;

interface ServiceManager
{
    public function start(): void;

    public function stop(): void;

    public function restart(): void;

    public function running(): bool;
}
