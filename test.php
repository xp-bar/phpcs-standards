<?php
namespace XpBar\Tests;

class Test
{
    /**
     * Test func
     *
     * @param int $one this is an $dollar integer
     * @param Test|null $notTyped
     * @param Model $reg
     * @return Hello
     */
    public function testFunc(int $one, ?Test $notTyped, Model $reg): Hello
    {
        $one = $notTyped;
        $one = $reg;
        return "{$one} string";
    }
}
