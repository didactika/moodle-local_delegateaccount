# Contributing to Delegate Account

Thank you for helping improve Delegate Account. Contributions are accepted under the
repository's [GNU GPL v3 or later license](LICENSE).

## Before you start

- Open a pull request directly for a small correction, isolated bug fix or test-only
  improvement.
- Discuss larger features, behavioural changes and architectural work before investing in
  an implementation.
- Report vulnerabilities through GitHub's private security advisory interface. Never put
  credentials, personal data or sensitive Moodle logs in a public issue.

## Development workflow

1. Branch from `main`. Use a maintained `MOODLE_XXX_STABLE` branch only for an intentional
   backport to that Moodle release line.
2. Choose a descriptive name such as `fix/revocation-events` or
   `feat/delegation-expiry`.
3. Follow the [Moodle coding style](https://moodledev.io/general/development/policies/codingstyle)
   and document every public API.
4. Add or update PHPUnit and Behat coverage whenever behaviour changes.
5. Keep all English, Spanish, Portuguese, Italian and French language packs aligned when
   adding or changing user-facing strings.
6. Rebuild and commit `amd/build/` whenever a module under `amd/src/` changes.
7. Update the README, changelog and integration guide when the public behaviour changes.
8. Open a pull request using the Didactika template. The required `CI complete` check must
   pass before merge.

## Local installation

The checkout directory must be named `delegateaccount`.

```bash
# Moodle 4.5.
git clone https://github.com/didactika/moodle-local_delegateaccount.git \
    /path/to/moodle/local/delegateaccount

# Moodle 5.0 and later.
git clone https://github.com/didactika/moodle-local_delegateaccount.git \
    /path/to/moodle/public/local/delegateaccount
```

Visit **Site administration > Notifications** or run Moodle's CLI upgrade after mounting
the plugin.

## Required checks

Run checks from the Moodle root, adjusting the plugin path for Moodle 5.0 and later:

```bash
vendor/bin/phpunit --testsuite local_delegateaccount_testsuite
vendor/bin/phpcs --standard=moodle --warning-severity=1 local/delegateaccount
```

The pull-request workflow runs the complete `moodle-plugin-ci` matrix, including PHPUnit,
Behat, PHPCS, PHPDoc, Mustache and Grunt checks against PostgreSQL and MariaDB.

## Commit messages

Use [Conventional Commits](https://www.conventionalcommits.org/):

```text
fix(events): isolate revocation audit records
feat(report): add delegation-period filters
docs: clarify web-service permissions
test(manager): cover notification event coexistence
```

Keep the summary imperative. Use the body to explain why the change is required and mark
incompatible changes explicitly with `BREAKING CHANGE:`.

## Pull requests

A pull request should state what changed, why it changed and how it was verified. Call out
database, privacy, capability, upgrade and Moodle-version implications explicitly. Keep the
change focused and resolve every required check and review comment before merge.
