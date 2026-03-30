---
name: requirement-registration
description: Adds a new add_requirement() call inside getRequirements() in src/Plugin.php with correct vendor-relative path format. Use when user says 'register requirement', 'add file dependency', 'add requirement', or references a new .php or .inc.php file that needs lazy-loading. Do NOT use for Composer package dependencies or autoloaded classes.
---
# requirement-registration

## Critical

- Paths MUST start with `/../vendor/detain/myadmin-piwik-analytics/src/` — the loader resolves from the vendor root, so the leading `/../` is required.
- The requirement name (first arg) for a class file must be `class.ClassName`; for procedural function files use the function name(s) the file defines.
- Multiple functions from the same file each get their own `add_requirement()` line pointing to the same path — do NOT consolidate them.
- Never modify the method signature of `getRequirements(GenericEvent $event)` — tests verify it accepts exactly one `GenericEvent` parameter.

## Instructions

1. **Identify the file and its primary symbol.** Determine whether the file defines a class or one or more procedural functions. Verify the file exists under `src/`.

2. **Choose the requirement name.**
   - Class file → `'class.ClassName'`
   - Procedural file → the function name it defines (e.g. `'deactivate_kcare'`). If it defines multiple functions, add one line per function, all pointing to the same path.

3. **Open `src/Plugin.php` and locate `getRequirements()`.** The method body starts with:
   ```php
   $loader = $event->getSubject();
   ```
   Append your new line(s) after the last existing `add_requirement()` call.

4. **Add the call using this exact format (tabs for indentation):**
   ```php
   		$loader->add_requirement('your_name', '/../vendor/detain/myadmin-piwik-analytics/src/YourFile.php');
   ```
   Match the indentation of surrounding lines (two tabs).

5. **Update `tests/PluginTest.php`.** In `testGetRequirementsRegistersExpectedRequirements()`, add an `assertContains` for each new requirement name:
   ```php
   $this->assertContains('your_name', $names);
   ```
   This step uses the name chosen in Step 2.

6. **Run tests to verify:**
   ```bash
   vendor/bin/phpunit tests/ -v
   ```
   All tests must pass before considering the task done.

## Examples

**User says:** "Register the `send_piwik_report` function from `src/reports.php`"

**Actions taken:**

In `src/Plugin.php`, inside `getRequirements()`, append:
```php
		$loader->add_requirement('send_piwik_report', '/../vendor/detain/myadmin-piwik-analytics/src/reports.php');
```

In `tests/PluginTest.php`, inside `testGetRequirementsRegistersExpectedRequirements()`, append:
```php
$this->assertContains('send_piwik_report', $names);
```

**Result:** `vendor/bin/phpunit` passes; the loader can now lazy-load `send_piwik_report` on demand.

## Common Issues

- **Path starts with `/vendor/` instead of `/../vendor/`** — the loader navigates up one level from the vendor root; omitting `/../` causes a file-not-found at runtime. Always use `'/../vendor/detain/...'`.
- **PHPUnit: `Failed asserting that an array contains 'your_name'`** — you added the `add_requirement()` call but forgot to add the corresponding `assertContains` in `testGetRequirementsRegistersExpectedRequirements()`. Add it and re-run.
- **PHPUnit: `testGetRequirementsAcceptsGenericEventParameter` fails** — the method signature was changed. Revert to `public static function getRequirements(GenericEvent $event)` with no other parameters.
- **Indentation error flagged by `.scrutinizer.yml`** — the project enforces tabs. If your editor inserted spaces, replace with tabs: `unexpanded_tabsize 4` is not acceptable; use a literal tab character.
