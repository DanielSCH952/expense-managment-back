<?php

namespace App;

enum HouseholdRole:string
{
    case ADMIN = 'admin';
    case MEMBER = 'member';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrador',
            self::MEMBER => 'Miembro',
        };
    }
}
