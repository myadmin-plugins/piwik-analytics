---
name: plugin-hook-registration
description: Adds a new Symfony GenericEvent hook to src/Plugin.php by updating getHooks() and adding the handler method. Use when user says 'add hook', 'register event', 'listen to event', or needs a new event handler method. Do NOT use for modifying or removing existing hooks. NOTE: for a plugin's contract/behavioral tests (tests/ContractTest.php, the shared harness, composer myadmin:scaffold-tests) use the plugin-contract-tests skill instead — this skill's reflection-only guidance predates that harness.
---
<!-- myadmin-contract-harness-notice -->
> ### ⚠️ Read this before the rest of the file
>
> This package is on the **shared plugin contract harness**. Parts of the guidance below
> predate it and are now wrong in one specific way:
>
> **Any instruction here that a plugin's `getHooks()` / `getSettings()` / `getActivate()` /
> `getDeactivate()` / `getQueue()` must not be *called* — that only its existence, visibility
> or parameter count may be checked through `ReflectionClass` — no longer applies.** That rule
> existed because those methods reference bare constants (`PRORATE_BILLING` and friends) that
> only a live MyAdmin request defines, so calling them from a test used to fatal. The harness
> defines them first. It then executes the handlers for real, in a process of its own.
>
> A reflection-only assertion passes whether or not the thing works: `getActivate()` can exist,
> be public, be static, take one argument, and still fatal the moment it runs. Three real
> production bugs in this fleet were sitting behind assertions of exactly that shape.
>
> **Use the `plugin-contract-tests` skill** for anything touching `tests/ContractTest.php`,
> the contract inspectors, or `composer myadmin:scaffold-tests`.
>
> **Everything else in this file is still accurate and still applies** — this package's own
> classes, its API wrappers, its fixtures, its bootstrap, and the reasons certain classes must
> not be constructed. Nothing below has been removed.

# Plugin Hook Registration

## Critical

- All handler methods MUST be `public static` — the test suite asserts this via `ReflectionClass`.
- Handler parameter MUST be typed as `GenericEvent $event` — reflection tests assert the exact type name.
- Event names in `getHooks()` MUST use lowercase dot-notation only (e.g. `analytics.settings`).
- Use `__CLASS__` (not `Plugin::class`) as the class reference in `getHooks()` return array.
- Indentation: **tabs only** — enforced by `.scrutinizer.yml`.
- No closing `?>`.

## Instructions

1. **Identify the event name.** Confirm it follows dot-notation (`group.action`, e.g. `analytics.boot`). Verify the event name is not already registered (or commented out) in `getHooks()` inside `src/Plugin.php`.

2. **Choose a handler method name.** Use camelCase, descriptive of the action (e.g. `getMenu`, `getSettings`, `getRequirements`). Verify no method with that name already exists in the class.

3. **Add the entry to `getHooks()`.** Open `src/Plugin.php` and add the event→handler pair to the returned array:

```php
public static function getHooks()
{
	return [
		'your.event.name' => [__CLASS__, 'yourHandlerMethod'],
	];
}
```

   Verify: the array key is a string in dot-notation; the value is a 2-element array `[__CLASS__, 'methodName']`.

4. **Add the handler method** to `src/Plugin.php` immediately after the existing handlers, before the closing `}`:

```php
/**
 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
 */
public static function yourHandlerMethod(GenericEvent $event)
{
	$subject = $event->getSubject();
	// implementation here
}
```

   Verify: method is `public static`, single parameter named `$event` typed `GenericEvent`.

5. **Run the test suite** to confirm structural tests pass:

```bash
vendor/bin/phpunit tests/ -v
```

   The `testGetHooksValuesReferencePluginClass` and `testAllEventHandlersAreStatic` tests must pass.

## Examples

**User says:** "Add a hook for the `piwik.boot` event that loads boot requirements."

**Actions taken:**

1. In `getHooks()`, add `'piwik.boot' => [__CLASS__, 'getBootRequirements']`.
2. Add handler method:

```php
/**
 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
 */
public static function getBootRequirements(GenericEvent $event)
{
	$loader = $event->getSubject();
	$loader->add_requirement('class.Piwik', '/../vendor/detain/myadmin-piwik-analytics/src/Piwik.php');
}
```

3. Run `vendor/bin/phpunit tests/ -v` — all tests pass.

**Result:** `getHooks()` returns `['piwik.boot' => [Plugin::class, 'getBootRequirements']]`; reflection tests confirm the method is public, static, and accepts `GenericEvent`.

## Common Issues

- **Test fails: `Handler method 'foo' for 'event.name' must exist on Plugin`** — you added the entry to `getHooks()` but forgot to add the actual handler method. Add the `public static function foo(GenericEvent $event)` method to the class.

- **Test fails: `Method 'yourMethod' should be static`** — handler was declared without `static`. Change `public function` to `public static function`.

- **Test fails: `assertMatchesRegularExpression` on event name** — event key contains uppercase or invalid characters. Use lowercase dot-notation only: `piwik.boot`, not `Piwik_Boot`.

- **PHP parse error after editing** — tab/space mix or missing comma after the last array entry. Ensure the array entry ends with a comma and indentation uses tabs.

- **`vendor/bin/phpunit` not found** — run `composer install` first to install dev dependencies.
