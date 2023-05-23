<?php

namespace App\Services;

use Illuminate\Support\Facades\Date;

class UserService
{
    public static function dateToHuman($date)
    {
        if ($date == null) {
            return null;
        }

        $newDate = '';
        for ($i = 0; $i < 10; $i++) {
            $newDate[$i] = $date[$i];
        }
        return $newDate;
    }

    public static function getGender($gender)
    {
        switch ($gender) {
            case 'male':
                return 'Мужчина';
            case 'female':
                return 'Женщина';
            default:
                return '';
        }
    }

    public static function dateToJson($date)
    {
        return $date . 'T00:00:00.000Z';
    }
}
