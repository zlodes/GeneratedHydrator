<?php

declare(strict_types=1);

namespace GeneratedHydratorTest\Factory;

use CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use CodeGenerationUtils\ReflectionBuilder\ClassBuilder;
use CodeGenerationUtils\Visitor\ClassRenamerVisitor;
use GeneratedHydrator\Configuration;
use GeneratedHydratorTestAsset\ClassWithMixedProperties;
use Laminas\Hydrator\HydratorInterface;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Integration tests for {@see \GeneratedHydrator\Factory\HydratorFactory}
 *
 * @group Functional
 */
class HydratorFactoryFunctionalTest extends TestCase
{
    protected Configuration $config;

    /** @psalm-var class-string */
    protected string $generatedClassName;

    public function setUp(): void
    {
        /** @psalm-var class-string $this->generatedClassName */
        $this->generatedClassName = UniqueIdentifierGenerator::getIdentifier('foo');
        $this->config             = new Configuration($this->generatedClassName);
        $generatorStrategy        = new EvaluatingGeneratorStrategy();
        $reflection               = new ReflectionClass(ClassWithMixedProperties::class);
        $generator                = new ClassBuilder();
        $traverser                = new NodeTraverser();
        $renamer                  = new ClassRenamerVisitor($reflection, $this->generatedClassName);

        $traverser->addVisitor($renamer);

        // evaluating the generated class
        //die(var_dump($traverser->traverse($generator->fromReflection($reflection))));
        $ast = $traverser->traverse($generator->fromReflection($reflection));
        $generatorStrategy->generate($ast);

        $this->config->setGeneratorStrategy($generatorStrategy);
    }

    /**
     * @covers \GeneratedHydrator\Factory\HydratorFactory::__construct
     * @covers \GeneratedHydrator\Factory\HydratorFactory::getHydratorClass
     */
    public function testWillGenerateValidClass(): void
    {
        $generatedClass = $this->config->createFactory()->getHydratorClass();

        self::assertInstanceOf(HydratorInterface::class, new $generatedClass());
    }

    /**
     * @covers \GeneratedHydrator\Factory\HydratorFactory::__construct
     * @covers \GeneratedHydrator\Factory\HydratorFactory::getHydrator
     */
    public function testWillInstantiateValidHydrator(): void
    {
        $factory       = $this->config->createFactory();
        $hydratorClass = $factory->getHydratorClass();
        $hydrator      = $factory->getHydrator();

        self::assertEquals(new $hydratorClass(), $hydrator);
    }
}
