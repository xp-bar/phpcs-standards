<?php
namespace XpBar\Tests;

class Test
{
    /**
     * Test func
     *
     * @param int $one
     * @param Star $notTyped
     * @param Model $reg
     */
    public function testFunc(int $one, Star $notTyped, Model $reg): \Illuminate\Star
    {
        $one = $notTyped;
        $one = $reg;
        return "{$one} string";
    }
}
