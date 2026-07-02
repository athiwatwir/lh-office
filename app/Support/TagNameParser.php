<?php

namespace App\Support;

class TagNameParser
{
    /**
     * @return list<string>
     */
    public static function parse(string $input): array
    {
        $names = [];

        foreach (preg_split('/[\r\n]+/', $input) ?: [] as $line) {
            foreach (preg_split('/[,;]+/', $line) ?: [] as $part) {
                $name = trim($part);

                if ($name !== '' && $name !== '-') {
                    $names[] = $name;
                }
            }
        }

        return array_values(array_unique($names));
    }
}
