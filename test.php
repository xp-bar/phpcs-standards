<?php
namespace XpBar\Tests;

class Test
{
    /**
     * Hello
     *
     * @param int $one
     * @param Star $notTyped A thing
     * @param Model $reg This is a reg
     * @return Model
     */
    public function testFunc(int $one, Star $notTyped, Model $reg): Model
    {
        $one = $notTyped;
        $one = $reg;
        return "{$one} string";
    }
}
