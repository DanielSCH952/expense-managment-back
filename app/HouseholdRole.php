<?php

namespace App;

enum HouseholdRole:string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case MEMBER = 'member';

    public function label(): string
    {
        return match($this) {
            self::OWNER => 'Propietario',
            self::ADMIN => 'Administrador',
            self::MEMBER => 'Miembro',
        };
    }
}
