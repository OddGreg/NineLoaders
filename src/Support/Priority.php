<?php namespace Nine\Loaders\Support;

use Nine\Loaders\Exceptions\InvalidPriorityTokenException;

final class Priority
{
    const HIGH   = 10;
    const NORMAL = 100;
    const LOW    = 1000;

    const PRIORITIES = [
        'high'   => self::HIGH,
        'normal' => self::NORMAL,
        'low'    => self::LOW,
    ];

    /**
     * @param $priority
     *
     * @return int|string
     * @throws InvalidPriorityTokenException
     */
    public static function resolve($priority)
    {
        if (is_string($priority)) {
            if ( ! array_key_exists(strtolower($priority), self::PRIORITIES)) {
                throw new InvalidPriorityTokenException(
                    "$priority is not a valid priority token. Try 'high', 'normal', 'low' or an integer value.");
            }

            return self::PRIORITIES[strtolower($priority)];
        }

        return (int)$priority;
    }

}
