<?php namespace Nine\Loaders;

use Nine\Loaders\Exceptions\InvalidPriorityTokenException;
use Nine\Loaders\Support\Priority;

/**
 * Test the Collection Class
 *
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class PriorityTest extends \PHPUnit_Framework_TestCase
{
    public function test_invalid_integer_priority_converted_to_int()
    {
        static::assertSame(1, Priority::resolve(1.0));

        static::assertSame(Priority::HIGH, Priority::resolve('HIGH'));
        static::assertSame(Priority::HIGH, Priority::resolve('high'));

        static::assertSame(Priority::NORMAL, Priority::resolve('normal'));
        static::assertSame(Priority::LOW, Priority::resolve('low'));
    }

    public function test_invalid_priority()
    {
        $this->expectException(InvalidPriorityTokenException::class);
        Priority::resolve('bogus');
    }

}
