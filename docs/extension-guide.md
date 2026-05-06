# Extension Guide

## Add Artifact Parsers

Recommended parser modules:

- SQLite table extractor for known databases.
- XML preference extractor.
- Media metadata reader.
- APK inventory normalizer.
- Timeline event normalizer.
- Hash manifest importer.

Keep parsers read-only and preserve original evidence files outside the application runtime.

## Add Case Exports

Recommended export sections:

- Scope and authority.
- Acquisition method.
- Tool versions.
- Hash manifests.
- Artifact findings.
- Timeline.
- Correlation table.
- Limitations.
- Confidence ratings.
- Chain-of-custody appendix.

## Add Authentication

Recommended roles:

- Viewer.
- Examiner.
- Lead examiner.
- Evidence custodian.
- Administrator.

Protect real case data before enabling multi-user use.

