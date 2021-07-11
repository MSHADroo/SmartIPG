<?php

namespace App\Helpers;


class BladeHelpers
{
    protected $container;
    protected $dal;

    public function __construct($container)
    {
        $this->container = $container;
    }

}