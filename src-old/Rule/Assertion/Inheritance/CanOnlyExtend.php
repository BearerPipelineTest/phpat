<?php

declare(strict_types=1);

namespace PHPatOld\Rule\Assertion\Inheritance;

use PHPatOld\Parser\Ast\ClassLike;
use PHPatOld\Parser\Ast\ReferenceMap;
use PHPatOld\Rule\Assertion\AbstractAssertion;
use PHPatOld\Statement\Event\StatementNotValidEvent;
use PHPatOld\Statement\Event\StatementValidEvent;

class CanOnlyExtend extends AbstractAssertion
{
    public function acceptsRegex(): bool
    {
        return true;
    }

    /**
     * @param array<ClassLike> $included
     * @param array<ClassLike> $excluded
     */
    public function validate(
        ClassLike $origin,
        array $included,
        array $excluded,
        ReferenceMap $map
    ): void {
        $matchingNodes = $this->filterMatchingNodes($origin, $map);

        foreach ($matchingNodes as $node) {
            $parent = $this->getParent($node, $map);
            if ($parent === null) {
                $this->dispatchResult(true, $node->getClassName());
                continue;
            }

            $result = $this->relationMatchesDestinations($parent, $included, $excluded);
            if (!$result->matched()) {
                $this->dispatchResult(false, $node->getClassName(), $parent);
            } else {
                $this->dispatchResult(true, $node->getClassName());
            }
        }
    }

    private function dispatchResult(bool $result, string $fqcnOrigin, string $fqcnDestination = ''): void
    {
        $message = $result
            ? $fqcnOrigin . ' does not extend forbidden classes'
            : $fqcnOrigin . ' extends ' . $fqcnDestination;
        $event = $result ? StatementValidEvent::class : StatementNotValidEvent::class;

        $this->eventDispatcher->dispatch(new $event($message));
    }
}
