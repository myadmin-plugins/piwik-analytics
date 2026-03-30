---
name: phpunit-plugin-test
description: Creates PHPUnit test methods in `tests/PluginTest.php` following the ReflectionClass-based pattern used in this project. Use when user says 'add test', 'write test for', 'test this method', or adds a new method to `Plugin`. Covers static property existence, method signature verification, and behavior using anonymous class stubs. Do NOT use for integration tests or tests outside the Plugin class.
---
# phpunit-plugin-test

## Critical

- **Never use Mockery or PHPUnit mocks** for the loader/settings subject — use anonymous classes (see existing pattern in `testGetRequirementsRegistersExpectedRequirements`).
- **All test methods must be `public function testXxx(): void`** — return type `void` is required.
- **File must begin with `declare(strict_types=1);`** — already present; do not remove it.
- **Indentation: tabs only** — enforced by `.scrutinizer.yml`. Never use spaces.
- **`$this->reflected`** is a `ReflectionClass<Plugin>` set up in `setUp()` — reuse it; do not instantiate a new one per test.
- Before adding a test for a method, verify the method exists in `src/Plugin.php`.

## Instructions

1. **Open the test file** `tests/PluginTest.php`. Identify the section comment block (e.g., `// --- getMenu() ---`) that matches the method under test. If none exists, append a new section at the bottom before the closing `}`.
   - Verify the file has `use ReflectionClass;` and `use Symfony\Component\EventDispatcher\GenericEvent;` in the imports.

2. **For a new static property** (`$name`, `$description`, `$help`, `$type`, or any new one added to `Plugin`):
   ```php
   public function testStaticPropertyFooExists(): void
   {
       $this->assertTrue($this->reflected->hasProperty('foo'));
       $this->assertSame('expected value', Plugin::$foo);
   }
   ```
   Also add `'foo'` to the loop inside `testStaticPropertiesArePublic()`.

3. **For a new event-handler method** (accepts `GenericEvent`, returns void), add three tests:

   **3a. Visibility + static:**
   ```php
   /** @covers ::myMethod */
   public function testMyMethodIsPublicStatic(): void
   {
       $method = $this->reflected->getMethod('myMethod');
       $this->assertTrue($method->isPublic());
       $this->assertTrue($method->isStatic());
   }
   ```

   **3b. Parameter signature:**
   ```php
   /** @covers ::myMethod */
   public function testMyMethodAcceptsGenericEventParameter(): void
   {
       $method = $this->reflected->getMethod('myMethod');
       $params = $method->getParameters();
       $this->assertCount(1, $params);
       $this->assertSame('event', $params[0]->getName());
       $type = $params[0]->getType();
       $this->assertNotNull($type);
       $this->assertSame(GenericEvent::class, $type->getName());
   }
   ```

   **3c. Behavior** — use an anonymous class stub as the event subject:
   ```php
   /** @covers ::myMethod */
   public function testMyMethodDoesNotThrow(): void
   {
       $subject = new class {};
       $event = new GenericEvent($subject);
       Plugin::myMethod($event);
       $this->assertTrue(true);
   }
   ```
   If the method calls methods on `$event->getSubject()` (like `add_requirement`), record calls in a `$recorded = []` array passed by reference into the anonymous class, then assert on `$recorded` after invocation.

4. **After adding tests**, update `testExpectedMethodsExist()` and `testAllEventHandlersAreStatic()` to include the new method name in their `$expected`/`$handlers` arrays.

5. **Run tests** to confirm all pass:
   ```bash
   vendor/bin/phpunit tests/ -v
   ```
   Verify zero failures and zero errors before finishing.

## Examples

**User says:** "Add a test for a new `getNotifications(GenericEvent $event)` method I added to Plugin."

**Actions taken:**
1. Read `src/Plugin.php` — confirm `getNotifications` exists as a public static method accepting `GenericEvent`.
2. Append a new section to `tests/PluginTest.php`:
```php
// ------------------------------------------------------------------
//  getNotifications() — event handler signature & static analysis
// ------------------------------------------------------------------

/** @covers ::getNotifications */
public function testGetNotificationsIsPublicStatic(): void
{
	$method = $this->reflected->getMethod('getNotifications');
	$this->assertTrue($method->isPublic());
	$this->assertTrue($method->isStatic());
}

/** @covers ::getNotifications */
public function testGetNotificationsAcceptsGenericEventParameter(): void
{
	$method = $this->reflected->getMethod('getNotifications');
	$params = $method->getParameters();
	$this->assertCount(1, $params);
	$this->assertSame('event', $params[0]->getName());
	$type = $params[0]->getType();
	$this->assertNotNull($type);
	$this->assertSame(GenericEvent::class, $type->getName());
}

/** @covers ::getNotifications */
public function testGetNotificationsDoesNotThrow(): void
{
	$subject = new class {};
	$event = new GenericEvent($subject);
	Plugin::getNotifications($event);
	$this->assertTrue(true);
}
```
3. Add `'getNotifications'` to both `$expected` in `testExpectedMethodsExist()` and `$handlers` in `testAllEventHandlersAreStatic()`.
4. Run `vendor/bin/phpunit tests/ -v` — all tests pass.

**Result:** Three new test methods covering visibility, parameter type, and no-throw behavior.

## Common Issues

- **`Error: Class "Detain\MyAdminPiwik\Plugin" not found`** — autoloader not loaded. Ensure `phpunit.xml.dist` bootstraps it.
- **`TypeError: Argument 1 passed to GenericEvent::__construct() must be an object`** — you passed a string/array as the subject. Wrap it in an anonymous class: `new class { public $prop = ...; }`.
- **`ReflectionException: Method myMethod does not exist`** — the method hasn't been added to `src/Plugin.php` yet. Add the method there first, then write the test.
- **Indentation errors flagged by Scrutinizer (`tab_width`)** — editor converted tabs to spaces. Run `:retab!` in vim or set your editor to use real tabs for this file.
- **Test passes locally but CI fails with `Cannot redeclare`** — a `function_requirements()` or global function inside a method body conflicts across test runs. If `Plugin` methods call globals, add a `@runInSeparateProcess` annotation to that test method.
