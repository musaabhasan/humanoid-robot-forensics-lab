# Database

The MySQL schema supports seeded research data and operational forensic casework.

## Tables

| Table | Purpose |
| --- | --- |
| `paper_references` | Formal paper citation metadata. |
| `workflow_stages` | Research-derived forensic workflow stages. |
| `artifact_sources` | Artifact catalog with source, path, value, and validation strategy. |
| `forensic_challenges` | Casework challenges derived from the paper. |
| `forensic_controls` | Weighted controls for defensibility scoring. |
| `challenge_control_map` | Many-to-many challenge-control mapping. |
| `case_assessments` | Case context, score, selected controls, selected artifacts, and JSON result. |
| `hash_diff_runs` | Baseline/final manifests and diff result payloads. |
| `chain_of_custody_events` | Evidence movement and handling records. |
| `audit_events` | Platform audit records. |

## Migration Order

1. `database/migrations/001_create_core_tables.sql`
2. `database/seeders/001_seed_research_data.sql`

Docker Compose mounts both scripts into the MySQL initialization directory in this order.

