# Testing

The repository includes lightweight checks that run without third-party PHP packages.

## Lint

```bash
php bin/lint.php
```

## Functional Tests

```bash
php bin/test.php
```

The tests verify:

- Formal paper DOI and citation.
- Catalog counts for workflow stages, artifact sources, challenges, and controls.
- Maximum control weight.
- Strong casework profile reaches high defensibility.
- Weak casework profile produces critical residual risk.
- Artifact prioritization reacts to imported-app and companion-device context.
- SHA-256 manifest differencing identifies added, removed, modified, and unchanged files.
- Migration and seed files include required tables and key artifacts.

## HTTP Smoke Test

```bash
php -S 127.0.0.1:8087 -t public
```

Then verify:

- `http://127.0.0.1:8087/health`
- `http://127.0.0.1:8087/`
- `http://127.0.0.1:8087/casework`
- `http://127.0.0.1:8087/artifacts`
- `http://127.0.0.1:8087/hash-diff`
- `http://127.0.0.1:8087/paper`
- `http://127.0.0.1:8087/api/summary`

