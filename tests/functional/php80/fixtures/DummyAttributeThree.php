<?php

namespace Tests\PhpAT\functional\php80\fixtures;

#[\Attribute] class DummyAttributeThree
{
    public function __construct(
        public string $someString,
        public int $someInt
    ) {}
}
