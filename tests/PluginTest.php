<?php

declare(strict_types=1);

namespace Detain\MyAdminPiwik\Tests;

use Detain\MyAdminPiwik\Plugin;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Test suite for the Detain\MyAdminPiwik\Plugin class.
 *
 * Covers class structure, static properties, hook registration,
 * event handler signatures, and static analysis of methods that
 * depend on external/global state.
 *
 * @coversDefaultClass \Detain\MyAdminPiwik\Plugin
 */
class PluginTest extends TestCase
{
    /**
     * Reflected class instance, reused across structural tests.
     *
     * @var ReflectionClass<Plugin>
     */
    private ReflectionClass $reflected;

    /**
     * Set up the reflection instance before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->reflected = new ReflectionClass(Plugin::class);
    }

    // ------------------------------------------------------------------
    //  Class structure
    // ------------------------------------------------------------------

    /**
     * Verify that the Plugin class can be instantiated.
     *
     * @covers ::__construct
     * @return void
     */
    public function testPluginCanBeInstantiated(): void
    {
        $plugin = new Plugin();
        $this->assertInstanceOf(Plugin::class, $plugin);
    }

    /**
     * Verify that the Plugin class resides in the expected namespace.
     *
     * @return void
     */
    public function testClassBelongsToCorrectNamespace(): void
    {
        $this->assertSame('Detain\MyAdminPiwik', $this->reflected->getNamespaceName());
    }

    /**
     * Verify that the Plugin class is not abstract and not an interface.
     *
     * @return void
     */
    public function testClassIsConcreteAndInstantiable(): void
    {
        $this->assertFalse($this->reflected->isAbstract());
        $this->assertFalse($this->reflected->isInterface());
        $this->assertTrue($this->reflected->isInstantiable());
    }

    // ------------------------------------------------------------------
    //  Static properties
    // ------------------------------------------------------------------

    /**
     * Verify the $name static property exists and holds the expected value.
     *
     * @return void
     */
    public function testStaticPropertyNameExists(): void
    {
        $this->assertTrue($this->reflected->hasProperty('name'));
        $this->assertSame('Piwik Plugin', Plugin::$name);
    }

    /**
     * Verify the $description static property exists and holds the expected value.
     *
     * @return void
     */
    public function testStaticPropertyDescriptionExists(): void
    {
        $this->assertTrue($this->reflected->hasProperty('description'));
        $this->assertSame('Allows handling of Piwik Analytics', Plugin::$description);
    }

    /**
     * Verify the $help static property exists and is an empty string.
     *
     * @return void
     */
    public function testStaticPropertyHelpExists(): void
    {
        $this->assertTrue($this->reflected->hasProperty('help'));
        $this->assertSame('', Plugin::$help);
    }

    /**
     * Verify the $type static property exists and holds the expected value.
     *
     * @return void
     */
    public function testStaticPropertyTypeExists(): void
    {
        $this->assertTrue($this->reflected->hasProperty('type'));
        $this->assertSame('plugin', Plugin::$type);
    }

    /**
     * Verify all static properties are public.
     *
     * @return void
     */
    public function testStaticPropertiesArePublic(): void
    {
        foreach (['name', 'description', 'help', 'type'] as $property) {
            $prop = $this->reflected->getProperty($property);
            $this->assertTrue($prop->isPublic(), "Property \${$property} should be public");
            $this->assertTrue($prop->isStatic(), "Property \${$property} should be static");
        }
    }

    // ------------------------------------------------------------------
    //  getHooks()
    // ------------------------------------------------------------------

    /**
     * Verify getHooks() returns an array.
     *
     * @covers ::getHooks
     * @return void
     */
    public function testGetHooksReturnsArray(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertIsArray($hooks);
    }

    /**
     * Verify getHooks() is a static method.
     *
     * @covers ::getHooks
     * @return void
     */
    public function testGetHooksIsStatic(): void
    {
        $method = $this->reflected->getMethod('getHooks');
        $this->assertTrue($method->isStatic());
    }

    /**
     * Verify that every value registered in getHooks() is a valid callable array
     * referencing the Plugin class.
     *
     * @covers ::getHooks
     * @return void
     */
    public function testGetHooksValuesReferencePluginClass(): void
    {
        $hooks = Plugin::getHooks();
        // When hooks are registered, each must reference a valid Plugin method.
        foreach ($hooks as $eventName => $handler) {
            $this->assertIsString($eventName, 'Hook event name must be a string');
            $this->assertIsArray($handler, "Handler for '{$eventName}' must be an array");
            $this->assertCount(2, $handler, "Handler for '{$eventName}' must have exactly 2 elements");
            $this->assertSame(Plugin::class, $handler[0], "Handler class for '{$eventName}' must be Plugin");
            $this->assertTrue(
                $this->reflected->hasMethod($handler[1]),
                "Handler method '{$handler[1]}' for '{$eventName}' must exist on Plugin"
            );
        }
        // An empty hooks array is a valid state (all hooks commented out).
        $this->assertIsArray($hooks);
    }

    /**
     * Verify that the hooks array keys (if any) are dot-separated event names.
     *
     * @covers ::getHooks
     * @return void
     */
    public function testGetHooksKeysAreDotNotation(): void
    {
        $hooks = Plugin::getHooks();
        foreach (array_keys($hooks) as $eventName) {
            $this->assertMatchesRegularExpression(
                '/^[a-z][a-z0-9_.]+$/i',
                $eventName,
                "Event name '{$eventName}' should follow dot-notation"
            );
        }
        // An empty hooks array trivially satisfies this constraint.
        $this->assertIsArray($hooks);
    }

    // ------------------------------------------------------------------
    //  getMenu() — event handler signature & static analysis
    // ------------------------------------------------------------------

    /**
     * Verify getMenu() is a public static method.
     *
     * @covers ::getMenu
     * @return void
     */
    public function testGetMenuIsPublicStatic(): void
    {
        $method = $this->reflected->getMethod('getMenu');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
    }

    /**
     * Verify getMenu() accepts exactly one parameter of type GenericEvent.
     *
     * @covers ::getMenu
     * @return void
     */
    public function testGetMenuAcceptsGenericEventParameter(): void
    {
        $method = $this->reflected->getMethod('getMenu');
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());
        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame(GenericEvent::class, $type->getName());
    }

    /**
     * Verify getMenu() return type is void (implicit).
     *
     * @covers ::getMenu
     * @return void
     */
    public function testGetMenuReturnsVoid(): void
    {
        $method = $this->reflected->getMethod('getMenu');
        $returnType = $method->getReturnType();
        // Method has no explicit return type declared — that is acceptable.
        // If a return type exists, it should be void.
        if ($returnType !== null) {
            $this->assertSame('void', $returnType->getName());
        } else {
            $this->assertNull($returnType);
        }
    }

    // ------------------------------------------------------------------
    //  getRequirements() — event handler signature & static analysis
    // ------------------------------------------------------------------

    /**
     * Verify getRequirements() is a public static method.
     *
     * @covers ::getRequirements
     * @return void
     */
    public function testGetRequirementsIsPublicStatic(): void
    {
        $method = $this->reflected->getMethod('getRequirements');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
    }

    /**
     * Verify getRequirements() accepts exactly one GenericEvent parameter.
     *
     * @covers ::getRequirements
     * @return void
     */
    public function testGetRequirementsAcceptsGenericEventParameter(): void
    {
        $method = $this->reflected->getMethod('getRequirements');
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());
        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame(GenericEvent::class, $type->getName());
    }

    /**
     * Verify getRequirements() calls add_requirement on the event subject.
     * Uses an anonymous class as a loader stub to avoid mocking vendor classes.
     *
     * @covers ::getRequirements
     * @return void
     */
    public function testGetRequirementsRegistersExpectedRequirements(): void
    {
        $recorded = [];
        $loader = new class ($recorded) {
            /** @var array<int, array{0: string, 1: string}> */
            private array $recorded;

            /**
             * @param array<int, array{0: string, 1: string}> $recorded
             */
            public function __construct(array &$recorded)
            {
                $this->recorded = &$recorded;
            }

            /**
             * @param string $name
             * @param string $path
             * @return void
             */
            public function add_requirement(string $name, string $path): void
            {
                $this->recorded[] = [$name, $path];
            }
        };

        $event = new GenericEvent($loader);
        Plugin::getRequirements($event);

        $this->assertNotEmpty($recorded, 'getRequirements should register at least one requirement');

        $names = array_column($recorded, 0);
        $this->assertContains('class.Piwik', $names);
        $this->assertContains('deactivate_kcare', $names);
        $this->assertContains('deactivate_abuse', $names);
        $this->assertContains('get_abuse_licenses', $names);
    }

    /**
     * Verify that all paths registered by getRequirements() are non-empty strings.
     *
     * @covers ::getRequirements
     * @return void
     */
    public function testGetRequirementsPathsAreNonEmptyStrings(): void
    {
        $recorded = [];
        $loader = new class ($recorded) {
            /** @var array<int, array{0: string, 1: string}> */
            private array $recorded;

            public function __construct(array &$recorded)
            {
                $this->recorded = &$recorded;
            }

            public function add_requirement(string $name, string $path): void
            {
                $this->recorded[] = [$name, $path];
            }
        };

        $event = new GenericEvent($loader);
        Plugin::getRequirements($event);

        foreach ($recorded as [$name, $path]) {
            $this->assertIsString($path, "Path for requirement '{$name}' must be a string");
            $this->assertNotEmpty($path, "Path for requirement '{$name}' must not be empty");
        }
    }

    // ------------------------------------------------------------------
    //  getSettings() — event handler signature & static analysis
    // ------------------------------------------------------------------

    /**
     * Verify getSettings() is a public static method.
     *
     * @covers ::getSettings
     * @return void
     */
    public function testGetSettingsIsPublicStatic(): void
    {
        $method = $this->reflected->getMethod('getSettings');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
    }

    /**
     * Verify getSettings() accepts exactly one GenericEvent parameter.
     *
     * @covers ::getSettings
     * @return void
     */
    public function testGetSettingsAcceptsGenericEventParameter(): void
    {
        $method = $this->reflected->getMethod('getSettings');
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());
        $type = $params[0]->getType();
        $this->assertNotNull($type);
        $this->assertSame(GenericEvent::class, $type->getName());
    }

    /**
     * Verify getSettings() does not throw when called with a plain object subject.
     *
     * @covers ::getSettings
     * @return void
     */
    public function testGetSettingsDoesNotThrow(): void
    {
        $settings = new class {
        };
        $event = new GenericEvent($settings);

        // Should complete without throwing.
        Plugin::getSettings($event);
        $this->assertTrue(true);
    }

    // ------------------------------------------------------------------
    //  Constructor
    // ------------------------------------------------------------------

    /**
     * Verify the constructor takes zero parameters.
     *
     * @covers ::__construct
     * @return void
     */
    public function testConstructorHasNoParameters(): void
    {
        $constructor = $this->reflected->getConstructor();
        $this->assertNotNull($constructor);
        $this->assertCount(0, $constructor->getParameters());
    }

    /**
     * Verify the constructor is public.
     *
     * @covers ::__construct
     * @return void
     */
    public function testConstructorIsPublic(): void
    {
        $constructor = $this->reflected->getConstructor();
        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPublic());
    }

    // ------------------------------------------------------------------
    //  Method inventory — ensure expected methods exist
    // ------------------------------------------------------------------

    /**
     * Verify that all expected methods are defined on the Plugin class.
     *
     * @return void
     */
    public function testExpectedMethodsExist(): void
    {
        $expected = ['__construct', 'getHooks', 'getMenu', 'getRequirements', 'getSettings'];
        foreach ($expected as $methodName) {
            $this->assertTrue(
                $this->reflected->hasMethod($methodName),
                "Plugin class should have method '{$methodName}'"
            );
        }
    }

    /**
     * Verify that all event-handler methods are static.
     *
     * @return void
     */
    public function testAllEventHandlersAreStatic(): void
    {
        $handlers = ['getHooks', 'getMenu', 'getRequirements', 'getSettings'];
        foreach ($handlers as $methodName) {
            $method = $this->reflected->getMethod($methodName);
            $this->assertTrue(
                $method->isStatic(),
                "Method '{$methodName}' should be static"
            );
        }
    }
}
