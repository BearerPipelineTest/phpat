<?php

declare(strict_types=1);

namespace PHPatOld\Test;

use PHPatOld\App\Event\FatalErrorEvent;
use PHPatOld\App\Exception\FatalErrorException;
use PHPatOld\Rule\Rule;
use PHPatOld\Rule\RuleCollection;
use PHPatOld\Rule\TestParser;
use Psr\EventDispatcher\EventDispatcherInterface;

abstract class ArchitectureTest implements TestInterface
{
    protected TestParser $newRule;
    private EventDispatcherInterface $eventDispatcher;

    final public function __construct(TestParser $builder, EventDispatcherInterface $eventDispatcher)
    {
        $this->newRule         = $builder;
        $this->eventDispatcher = $eventDispatcher;
    }

    final public function __invoke(): RuleCollection
    {
        $rules = new RuleCollection();
        foreach (get_class_methods($this) as $method) {
            if (preg_match('/^(test)([_A-Za-z0-9])+$/', $method)) {
                try {
                    $rule = $this->invokeTest($method);
                } catch (\Exception $e) {
                    $this->eventDispatcher->dispatch(new FatalErrorEvent($e->getMessage()));
                    throw new FatalErrorException();
                }
                $rule->setName($this->beautifyMethodName($method));
                $rules->addValue($rule);
            }
        }

        return $rules;
    }

    protected function invokeTest(string $method): Rule
    {
        $rule = $this->{$method}();

        if (!($rule instanceof Rule)) {
            $message = $method . ' must return an instance of ' . Rule::class . '.';

            $this->eventDispatcher->dispatch(new FatalErrorEvent($message));
            throw new FatalErrorException();
        }

        if ($rule->getAssertion() === null) {
            $message = $method
                . ' is not properly defined. Please make sure that you define one of the assertion methods'
                . '(e.g. `mustImplement` or `mustNotDependOn`) to declare the assertion of the rule.';

            $this->eventDispatcher->dispatch(new FatalErrorEvent($message));
            throw new FatalErrorException();
        }

        return $rule;
    }

    private function beautifyMethodName(string $methodName): string
    {
        return ucfirst(
            ltrim(
                str_replace(
                    '_',
                    ' ',
                    preg_replace('/(?<!\ )[A-Z]/', '_$0', $methodName)
                ),
                'test '
            )
        );
    }
}
