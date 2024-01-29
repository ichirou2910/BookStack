<?php

namespace BookStack\Entities\Tools;

class DevopsUser
{
    public string $username;
    public string $name;

    public function __construct(string $username, string $name)
    {
        $this->username = $username;
        $this->name = $name;
    }
}
