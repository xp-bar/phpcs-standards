<?php
namespace XpBar\Tests;

class Test
{
    /**
     * Test func
     *
     * @param int $one this is an $dollar integer
     * @param Test $notTyped
     * @param Model $reg
     * @return Model
     */
    public function testFunc(int $one, Test $notTyped, Model $reg): ?Model
    {
        $one = $notTyped;
        $one = $reg;
        echo $one;
        return "{$one} string";
    }
}
