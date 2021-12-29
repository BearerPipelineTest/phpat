<?php

namespace Tests\PhpAT\functional\php80\architecture;

use PhpAT\Rule\Rule;
use PhpAT\Selector\Selector;
use PhpAT\Test\ArchitectureTest;
use Tests\PhpAT\functional\php80\fixtures\ClassWithAttribute;
use Tests\PhpAT\functional\php80\fixtures\ConstructorPromotionClass;
use Tests\PhpAT\functional\php80\fixtures\DummyAttributeOne;
use Tests\PhpAT\functional\php80\fixtures\DummyAttributeThree;
use Tests\PhpAT\functional\php80\fixtures\DummyAttributeTwo;
use Tests\PhpAT\functional\php80\fixtures\DummyException;
use Tests\PhpAT\functional\php80\fixtures\MatchClass;
use Tests\PhpAT\functional\php80\fixtures\NamedArgumentClass;
use Tests\PhpAT\functional\php80\fixtures\SimpleClassFive;
use Tests\PhpAT\functional\php80\fixtures\SimpleClassFour;
use Tests\PhpAT\functional\php80\fixtures\SimpleClassOne;
use Tests\PhpAT\functional\php80\fixtures\SimpleClassSix;
use Tests\PhpAT\functional\php80\fixtures\SimpleClassThree;
use Tests\PhpAT\functional\php80\fixtures\SimpleClassTwo;
use Tests\PhpAT\functional\php80\fixtures\UnionClass;

class ClassDependencyTest extends ArchitectureTest
{
    public function testUnionTypesAreCatched(): Rule
    {
        return $this->newRule
            ->classesThat(Selector::haveClassName(UnionClass::class))
            ->mustOnlyDependOn()
            ->classesThat(Selector::haveClassName(SimpleClassOne::class))
            ->andClassesThat(Selector::haveClassName(SimpleClassTwo::class))
            ->andClassesThat(Selector::haveClassName(SimpleClassThree::class))
            ->andClassesThat(Selector::haveClassName(SimpleClassFour::class))
            ->andClassesThat(Selector::haveClassName(SimpleClassFive::class))
            ->andClassesThat(Selector::haveClassName(SimpleClassSix::class))
            ->build();
    }

    public function testPromotedPropertiesAreCatched(): Rule
    {
        return $this->newRule
            ->classesThat(Selector::haveClassName(ConstructorPromotionClass::class))
            ->mustOnlyDependOn()
            ->classesThat(Selector::haveClassName(SimpleClassOne::class))
            ->andClassesThat(Selector::haveClassName(SimpleClassTwo::class))
            ->andClassesThat(Selector::haveClassName(SimpleClassThree::class))
            ->andClassesThat(Selector::haveClassName(SimpleClassFour::class))
            ->build();
    }

    public function testNamedArgumentsAreCatched(): Rule
    {
        return $this->newRule
            ->classesThat(Selector::haveClassName(NamedArgumentClass::class))
            ->mustOnlyDependOn()
            ->classesThat(Selector::haveClassName(UnionClass::class))
            ->andClassesThat(Selector::haveClassName(SimpleClassFive::class))
            ->build();
    }

    public function testMatchesWithThrowAreCatched(): Rule
    {
        return $this->newRule
            ->classesThat(Selector::haveClassName(MatchClass::class))
            ->mustOnlyDependOn()
            ->classesThat(Selector::haveClassName(SimpleClassOne::class))
            ->andClassesThat(Selector::haveClassName(SimpleClassTwo::class))
            ->andClassesThat(Selector::haveClassName(DummyException::class))
            ->build();
    }

    public function testAttributesAreCatched(): Rule
    {
        return $this->newRule
            ->classesThat(Selector::haveClassName(ClassWithAttribute::class))
            ->mustOnlyDependOn()
            ->classesThat(Selector::haveClassName(DummyAttributeOne::class))
            ->classesThat(Selector::haveClassName(DummyAttributeTwo::class))
            ->classesThat(Selector::haveClassName(DummyAttributeThree::class))
            ->build();
    }

    public function testInternalAttributeClassIsIgnored(): Rule
    {
        return $this->newRule
            ->classesThat(Selector::haveClassName(DummyAttributeTwo::class))
            ->canOnlyDependOn()
            ->classesThat(Selector::haveClassName(DummyAttributeTwo::class))
            ->build();
    }
}
