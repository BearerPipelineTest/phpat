<?php

namespace PHPatOld\Parser\Ast;

use PHPatOld\App\Configuration;
use PHPatOld\App\Event\FatalErrorEvent;
use PHPatOld\App\Exception\FatalErrorException;
use PHPatOld\App\Helper\PathNormalizer;
use PHPatOld\File\FileFinder;
use PHPatOld\Parser\Ast\Classmap\Classmap;
use PHPatOld\Parser\Ast\Traverser\TraverseContext;
use PHPatOld\Parser\Ast\Traverser\TraverserFactory;
use PHPatOld\Parser\ComposerFileParser;
use PHPatOld\PhpStubsMap\PhpStubsMap;
use PhpParser\Parser;
use Psr\EventDispatcher\EventDispatcherInterface;

class MapBuilder
{
    private FileFinder $finder;
    private Parser $parser;
    private TraverserFactory $traverserFactory;
    private EventDispatcherInterface $eventDispatcher;
    private ComposerFileParser $composerFileParser;
    private Configuration $configuration;

    public function __construct(
        FileFinder $finder,
        Parser $parser,
        TraverserFactory $traverserFactory,
        EventDispatcherInterface $eventDispatcher,
        ComposerFileParser $composerFileParser,
        Configuration $configuration
    ) {
        $this->finder             = $finder;
        $this->parser             = $parser;
        $this->traverserFactory   = $traverserFactory;
        $this->eventDispatcher    = $eventDispatcher;
        $this->composerFileParser = $composerFileParser;
        $this->configuration      = $configuration;
    }

    public function build(): ReferenceMap
    {
        return new ReferenceMap($this->buildSrcMap(), $this->buildExtensionMap(), $this->buildComposerMap());
    }

    /*
     * @return array<string, SrcNode>
     */
    private function buildSrcMap(): array
    {
        $files = $this->finder->findPhpFilesInPath(
            $this->configuration->getSrcPath(),
            $this->configuration->getSrcExcluded()
        );
        $traverser = $this->traverserFactory->create();

        foreach ($files as $file) {
            $pathname = PathNormalizer::normalizePathname($file->getPathname());
            $parsed   = $this->parser->parse(file_get_contents($pathname));
            TraverseContext::startFile($pathname);
            $traverser->traverse($parsed);
        }

        return Classmap::getClassmap();
    }

    /**
     * @return array<ClassLike>
     */
    private function buildExtensionMap(): array
    {
        return array_map(
            function (string $class) {
                return FullClassName::createFromFQCN($class);
            },
            array_keys(PhpStubsMap::CLASSES)
        );
    }

    /**
     * @throws FatalErrorException
     * @return array<string, ComposerPackage>
     */
    private function buildComposerMap(): array
    {
        $packages = $this->configuration->getComposerConfiguration();

        $result = [];
        foreach ($packages as $alias => $files) {
            $composerJson = $files['json'];
            $composerLock = $files['lock'] ?? substr($composerJson, 0, -5) . '.lock';

            if ($this->assertComposerPackage($alias, $composerJson, $composerLock) === false) {
                continue;
            }

            try {
                $parsed = $this->composerFileParser->parse($composerJson, $composerLock);
            } catch (\Throwable $e) {
                $this->eventDispatcher->dispatch(
                    new FatalErrorEvent('Error parsing "' . $alias . '" composer files')
                );
                throw new FatalErrorException();
            }

            $result[$alias] = new ComposerPackage(
                $alias,
                $this->convertNamespacesToClassLikes($parsed->getNamespaces(false)),
                $this->convertNamespacesToClassLikes($parsed->getNamespaces(true)),
                $this->convertNamespacesToClassLikes($parsed->getDeepRequirementNamespaces(false)),
                $this->convertNamespacesToClassLikes($parsed->getDeepRequirementNamespaces(true))
            );
        }

        return $result;
    }

    /**
     * @param array<string> $namespaces
     * @return array<ClassLike>
     */
    private function convertNamespacesToClassLikes(array $namespaces): array
    {
        return array_map(
            function (string $namespace) {
                return new RegexClassName($namespace . '*');
            },
            $namespaces
        );
    }

    private function assertComposerPackage(string $alias, string $composerJson, string $composerLock): bool
    {
        if (!is_file($composerJson)) {
            return false;
            //TODO: Fail only when rules include composer selectors
            //$error = new FatalErrorEvent('Composer package "' . $alias . '" is not properly configured');
            //$this->eventDispatcher->dispatch($error);
            //throw new FatalErrorException();
        }

        if (!is_file($composerLock)) {
            return false;
            //TODO: Fail only when rules include composer selectors
            //$error = new FatalErrorEvent('Unable to find the composer package "' . $alias . '" lock file');
            //$this->eventDispatcher->dispatch($error);
            //throw new FatalErrorException();
        }

        return true;
    }
}
