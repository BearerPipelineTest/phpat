<?php

declare(strict_types=1);

namespace PhpAT\Rule\Assertion\Mixin;

use PHPAT\EventDispatcher\EventDispatcher;
use PhpAT\Parser\Ast\ClassLike;
use PhpAT\Parser\Ast\ReferenceMap;
use PhpAT\Rule\Assertion\AbstractAssertion;
use PhpAT\Statement\Event\StatementNotValidEvent;
use PhpAT\Statement\Event\StatementValidEvent;

class MustInclude extends AbstractAssertion
{
    public function acceptsRegex(): bool
    {
        return false;
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
            $traits = $this->getTraits($node, $map);

            foreach ($included as $destination) {
                $result = $this->destinationMatchesRelations($destination, $excluded, $traits);
                if ($result->matched()) {
                    foreach ($result->getMatches() as $match) {
                        $this->dispatchResult(true, $node->getClassName(), $match);
                    }
                } else {
                    $this->dispatchResult(false, $node->getClassName(), $destination->toString());
                }
            }
        }
    }

    protected function getEventClassName(bool $includes): string
    {
        return $includes ? StatementValidEvent::class : StatementNotValidEvent::class;
    }

    private function dispatchResult(bool $result, string $fqcnOrigin, string $fqcnDestination): void
    {
        $action  = $result ? ' includes ' : ' does not include ';
        $event   = $this->getEventClassName($result);
        $message = $fqcnOrigin . $action . $fqcnDestination;

        $this->eventDispatcher->dispatch(new $event($message));
    }
}
