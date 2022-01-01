<?php

namespace Tests\PhpAT\unit\Rule\Assertion;

use PhpAT\App\Configuration;
use PHPAT\EventDispatcher\EventDispatcher;
use PhpAT\Parser\Ast\ClassLike;
use PhpAT\Parser\Ast\ComposerPackage;
use PhpAT\Parser\Ast\FullClassName;
use PhpAT\Parser\Ast\ReferenceMap;
use PhpAT\Parser\Ast\SrcNode;
use PhpAT\Parser\Relation\Composition;
use PhpAT\Parser\Relation\Dependency;
use PhpAT\Parser\Relation\Inheritance;
use PhpAT\Parser\Relation\Mixin;
use PhpAT\Rule\Assertion\AbstractAssertion;
use PhpAT\Statement\Event\StatementNotValidEvent;
use PhpAT\Statement\Event\StatementValidEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

abstract class AbstractAssertionTestCase extends TestCase
{
    abstract public function dataProvider(): array;

    /**
     * @dataProvider dataProvider
     * @param ClassLike    $origin The selected class in which to perform assertions
     * @param array<ClassLike> $included Classes that must be in the relation test
     * @param array<ClassLike> $excluded Classes excluded from the relation test
     * @param ReferenceMap $map The fake reference map
     * @param array<bool>       $expectedEvents Expected ordered assertion results (true = valid , false = invalid)
     */
    public function testDispatchesCorrectEvents(
        ClassLike $origin,
        array $included,
        array $excluded,
        ReferenceMap $map,
        array $expectedEvents
    ): void {
        /** @var EventDispatcherInterface|MockObject $eventDispatcherMock */
        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock->method('getIgnorePhpExtensions')->willReturn(true);
        $className = $this->getTestedClassName();
        /** @var AbstractAssertion $class */
        $class = new $className($eventDispatcherMock, $configurationMock);

        foreach ($expectedEvents as $valid) {
            $eventType     = $valid ? StatementValidEvent::class : StatementNotValidEvent::class;
            $consecutive[] = [$this->isInstanceOf($eventType)];
        }

        $eventDispatcherMock
            ->expects($this->exactly(count($consecutive ?? [])))
            ->method('dispatch')
            ->withConsecutive(...$consecutive ?? []);

        $class->validate($origin, $included, $excluded, $map);
    }

    abstract protected function getTestedClassName(): string;

    /**
     * Fake ReferenceMap for the tests
     */
    protected function getMap(): ReferenceMap
    {
        return new ReferenceMap(
            [
                'Example\\ClassExample' => new SrcNode(
                    'folder/Example/ClassExample.php',
                    new FullClassName('Example\\ClassExample'),
                    [
                        new Inheritance(new FullClassName('Example\\ParentClassExample'), 0, 0),
                        new Inheritance(new FullClassName('\\FilterIterator'), 0, 0),
                        new Dependency(new FullClassName('Example\\AnotherClassExample'), 0, 0),
                        new Dependency(new FullClassName('Vendor\\ThirdPartyExample'), 0, 0),
                        new Dependency(new FullClassName('iterable'), 0, 0),
                        new Composition(new FullClassName('Example\\InterfaceExample'), 0, 0),
                        new Composition(new FullClassName('Example\\AnotherInterface'), 0, 0),
                        new Composition(new FullClassName('iterable'), 0, 0),
                        new Mixin(new FullClassName('Example\\TraitExample'), 0, 0),
                        new Mixin(new FullClassName('PHPDocElement'), 0, 0)
                    ]
                )
            ],
            [
                new FullClassName('iterable'),
                new FullClassName('\\FilterIterator'),
                new FullClassName('PHPDocElement'),
            ],
            [
                new ComposerPackage('main', [], [], [], [])
            ]
        );
    }
}
