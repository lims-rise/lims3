<?php

namespace App\Support;

class Lab
{
    public const LABS = [1 => 'Indonesia', 2 => 'Fiji'];

    public static function current(): string
    {
        $lab = (string) session('lab', '');

        if (! isset(self::LABS[$lab])) {
            $userLab = (string) (auth()->user()->lab ?? '');
            $lab = isset(self::LABS[$userLab]) ? $userLab : '1';
            session(['lab' => $lab]);
        }

        return $lab;
    }

    public static function name(): string
    {
        return self::LABS[(int) self::current()];
    }
}