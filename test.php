<?php
namespace XpBar\Tests;

use Event;

class Test
{
    /**
     * Test func
     *
     * @param int $one this is an $dollar integer
     * @param Test $notTyped
     * @param Model $reg
     * @return Model|null
     */
    public function testFunc(int $one, Test $notTyped, Model $reg): ?Model
    {
        $one = $notTyped;
        $one = $reg;
        Event::find(1);
        echo $one;
        return "{$one} string";
    }


    /**
     * Mixed Return
     *
     * @return mixed
     */
    public function mixed()
    {
    }
}
