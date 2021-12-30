<?php

namespace Tests\PhpAT\functional\php7\architecture;

use PhpAT\Rule\Rule;
use PhpAT\Selector\Selector;
use PhpAT\Test\ArchitectureTest;

class MixinTest extends ArchitectureTest
{
    public function testSimpleTraitInclusion(): Rule
    {
        return $this->newRule
            ->classesThat(Selector::havePath('*/Mixin/IncludeTrait.php'))
            ->mustInclude()
            ->classesThat(Selector::havePath('tests/functional/php7/fixtures/SimpleTrait.php'))
            ->build();
    }

    public function testMultipleTraitsInclusion(): Rule
    {
        return $this->newRule
            ->classesThat(Selector::havePath('*/Mixin/IncludeMultipleTraits.php'))
            ->mustInclude()
            ->classesThat(Selector::havePath('tests/functional/php7/fixtures/SimpleTrait.php'))
            ->andClassesThat(
                Selector::havePath('tests/functional/php7/fixtures/Mixin/MixinNamespaceSimpleTrait.php')
            )
            ->build();
    }
}
