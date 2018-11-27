<?php
namespace XpBar\Tests;

class Test
{
    /**
     * Hello
     *
     * @param int $one
     * @param Star $notTyped
     * @param Model $reg This is a reg
     * @return \XpBar\Tests\Test
     */
    public function testFunc(int $one, Model $notTyped, Model $reg): Test
    {
        $one = $notTyped;
        $one = $reg;
        return "{$one} string";
    }
}
