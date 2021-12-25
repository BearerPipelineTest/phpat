<?php

declare(strict_types=1);

namespace PhpAT\Selector;

use PhpAT\App\Event\WarningEvent;
use PHPAT\EventDispatcher\EventDispatcher;
use PhpAT\Parser\Ast\ClassLike;
use PhpAT\Parser\Ast\ReferenceMap;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class SelectorResolver
 *
 * @package PhpAT\Selector
 */
class SelectorResolver
{
    private ContainerBuilder $container;
    private EventDispatcherInterface $dispatcher;

    /**
     * SelectorResolver constructor.
     */
    public function __construct(ContainerBuilder $container, EventDispatcherInterface $dispatcher)
    {
        $this->container = $container;
        $this->dispatcher = $dispatcher;
    }

    public function resolve(SelectorInterface $selector, ReferenceMap $map): array
    {
        $known = SelectorStorage::getKnown(get_class($selector), $selector->getParameter());
        if ($known !== null) {
            $this->warnOnEmptyResult($selector, $known);
            return $known;
        }

        foreach ($selector->getDependencies() as $dependency) {
            try {
                $d[$dependency] = $this->container->get($dependency);
            } catch (\Throwable $e) {
            }
        }

        $selector->injectDependencies($d ?? []);
        $selector->setReferenceMap($map);
        $selected = $selector->select();
        $this->warnOnEmptyResult($selector, $selected);

        SelectorStorage::registerOrigin(get_class($selector), $selector->getParameter(), $selected);

        return $selected;
    }

    /**
     * @param array<ClassLike> $selected
     */
    private function warnOnEmptyResult(SelectorInterface $selector, array $selected): void
    {
        if (empty($selected)) {
            $this->dispatcher->dispatch(
                new WarningEvent(
                    get_class($selector) . ' (' . $selector->getParameter() . ')' . ' could not find any class'
                )
            );
        }
    }
}
