<?php

namespace Tests\PHPat\unit\Rule\Assertion\Composition;

use PHPat\Parser\Ast\FullClassName;
use PHPat\Parser\Ast\RegexClassName;
use PHPat\Rule\Assertion\Composition\MustImplement;
use Tests\PHPat\unit\Rule\Assertion\AbstractAssertionTestCase;

class MustImplementTest extends AbstractAssertionTestCase
{
    public function dataProvider(): array
    {
        return [
            [
                FullClassName::createFromFQCN('Example\ClassExample'),
                [FullClassName::createFromFQCN('Example\InterfaceExample')],
                [],
                $this->getMap(),
                [true]
            ],
            [
                FullClassName::createFromFQCN('Example\ClassExample'),
                [FullClassName::createFromFQCN('Example\AnotherInterface')],
                [],
                $this->getMap(),
                [true]
            ],
            [
                FullClassName::createFromFQCN('Example\ClassExample'),
                [
                    FullClassName::createFromFQCN('Example\InterfaceExample'),
                    FullClassName::createFromFQCN('Example\AnotherInterface')
                ],
                [],
                $this->getMap(),
                [true, true]
            ],
            //it fails because regex Example\Another* is excluded
            [
                FullClassName::createFromFQCN('Example\ClassExample'),
                [
                    FullClassName::createFromFQCN('Example\InterfaceExample'),
                    FullClassName::createFromFQCN('Example\AnotherInterface')
                ],
                [new RegexClassName('Example\Another*')],
                $this->getMap(),
                [true, false]
            ],
            //it fails because NotARealInterface is not implemented
            [
                FullClassName::createFromFQCN('Example\ClassExample'),
                [FullClassName::createFromFQCN('NotARealInterface')],
                [],
                $this->getMap(),
                [false]
            ],
            //it fails twice because any of them are implemented
            [
                FullClassName::createFromFQCN('Example\ClassExample'),
                [
                    FullClassName::createFromFQCN('NopesOne'),
                    FullClassName::createFromFQCN('NopesTwo')
                ],
                [],
                $this->getMap(),
                [false, false]
            ]
       ];
    }
    protected function getTestedClassName(): string
    {
        return MustImplement::class;
    }
}
