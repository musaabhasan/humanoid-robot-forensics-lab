# Humanoid Robot Forensics Lab

A PHP 8.x and MySQL 8.0 forensic casework platform for humanoid robot and IoT evidence triage.

The project is based on **"Forensic Investigation of Humanoid Social Robot: A Case Study on Zenbo Robot"** by Farkhund Iqbal, Abdullah Kazim, Aine MacDermott, Richard Ikuesan, Musaab Hasan, and Andrew Marrington. It translates the paper's Zenbo robot forensic methodology into a practical platform for case assessment, artifact prioritization, SHA-256 manifest comparison, chain-of-custody readiness, and forensic reporting.

## Paper Reference

Iqbal, F., Kazim, A., MacDermott, A., Ikuesan, R., Hasan, M., & Marrington, A. (2024). **Forensic Investigation of Humanoid Social Robot: A Case Study on Zenbo Robot**. In *ARES 2024 - 19th International Conference on Availability, Reliability and Security, Proceedings*, Article 194. Association for Computing Machinery. https://doi.org/10.1145/3664476.3670906

Official records:

- ACM DOI: https://doi.org/10.1145/3664476.3670906
- ZU Scholars: https://zuscholars.zu.ac.ae/works/6716/
- Research portal record: https://nchr.elsevierpure.com/en/publications/forensic-investigation-of-humanoid-social-robot-a-case-study-on-z/

## What This Repository Provides

- Humanoid robot forensic defensibility assessment.
- Artifact catalog for Zenbo Master, Zenbo App Builder, robot databases, imported applications, voice responses, reminders, media, logs, package inventory, camera/audio profiles, and Android settings.
- SHA-256 manifest differencing for baseline and final logical acquisitions.
- Challenge model for distributed evidence, logical acquisition limits, root-restricted app data, timestamp friction, absent network identifiers, privacy risk, artifact tampering, and tool repeatability.
- 28 forensic controls across governance, evidence handling, scoping, acquisition, integrity, validation, analysis, timeline, application review, media review, privacy, and reporting.
- MySQL schema and seed data for paper metadata, workflow stages, artifacts, controls, challenge mappings, case assessments, hash-diff runs, chain-of-custody events, and audit events.
- JSON APIs for dashboard integration or research extension.
- Security-conscious PHP implementation with CSRF validation, security headers, input normalization, PDO prepared statements, secure cookies, and JSON-safe persistence.
- Linting, functional tests, HTTP smoke-test compatibility, and database migration validation.

## Quick Start

```bash
cp .env.example .env
docker compose up --build
```

Then open:

- Application: `http://localhost:8087`
- Casework: `http://localhost:8087/casework`
- Artifacts: `http://localhost:8087/artifacts`
- Hash diff: `http://localhost:8087/hash-diff`
- Paper alignment: `http://localhost:8087/paper`
- Health endpoint: `http://localhost:8087/health`
- JSON summary: `http://localhost:8087/api/summary`

## Local Checks

```bash
php bin/lint.php
php bin/test.php
```

## JSON APIs

Case assessment:

```bash
curl -X POST http://localhost:8087/api/assess \
  -H "Content-Type: application/json" \
  -d '{
    "case_name": "Zenbo witness review",
    "robot_model": "Zenbo Robot",
    "acquisition_mode": "logical-plus-companion",
    "paired_smartphone": true,
    "remote_interactions": true,
    "suspected_imported_app": true,
    "privacy_sensitive": true,
    "network_context_available": true,
    "court_facing_report": true,
    "artifacts": ["zenbo-master-videophone-db", "blockly-engine-db", "robot-user-profile-db"],
    "controls": ["case-authorization", "chain-of-custody", "baseline-hash-set", "final-hash-set", "hash-differencing"]
  }'
```

Hash comparison:

```bash
curl -X POST http://localhost:8087/api/hash-diff \
  -H "Content-Type: application/json" \
  -d '{
    "baseline": [
      {"path": "/system/build.prop", "sha256": "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa"}
    ],
    "final": [
      {"path": "/system/build.prop", "sha256": "bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb"}
    ]
  }'
```

## Repository Structure

```text
public/              Web entry point and responsive UI assets
src/                 PHP services, repository, security, and support classes
config/              Paper metadata, forensic workflow, controls, challenges, and artifact catalog
database/            MySQL migration and seed scripts
docs/                Architecture, paper alignment, security, testing, database, and extension notes
bin/                 Lint and functional test scripts
```

## Documentation

- [Robot and IoT Forensic Data Source Triage](docs/robot-iot-data-source-triage.md)
- [Architecture](docs/architecture.md)
- [Paper Alignment](docs/paper-alignment.md)
- [Security](docs/security.md)
- [Testing](docs/testing.md)
- [Database](docs/database.md)
- [Extension Guide](docs/extension-guide.md)

## Responsible Use

This repository is designed for authorized digital forensic research, evidence handling, and defensive casework planning. Do not use it to access, extract, or analyze devices without proper authorization, consent, and legal basis.

## Production Notes

- Add authentication and role-based access before storing real case data.
- Place the application behind HTTPS and a trusted reverse proxy.
- Store secrets outside source control.
- Encrypt evidence records, manifests, exported reports, and sensitive artifact notes.
- Preserve tool versions, acquisition notes, chain-of-custody records, and hash manifests with every case.
- Treat user profiles, relatives, reminders, media, device identifiers, and household context as sensitive information.
- Review findings, assumptions, and limitations before formal reporting.

## License

MIT License. See [LICENSE](LICENSE).

<!-- portfolio:start -->
## Portfolio and Professional Profile

This repository is part of the professional portfolio of [Musaab Hasan](https://musaab.info), focused on cybersecurity, digital forensics, AI governance, EdTech, secure platforms, and research-driven digital transformation.

### Digital Forensics and Security Research Labs

- [Android Digital Forensics Lab](https://github.com/musaabhasan/android-forensics-lab) - Advanced Android forensics workbench for acquisition planning, anti-forensics evaluation, memory triage, evidence integrity, and case reconstruction.
- [Humanoid Robot Forensics Lab](https://github.com/musaabhasan/humanoid-robot-forensics-lab) - PHP/MySQL forensic casework platform for humanoid robot, companion app, and IoT evidence triage.
- [Smart Metering Security Lab](https://github.com/musaabhasan/smart-metering-security-lab) - Research portal based on smart metering security analysis for cyber-physical and smart-grid environments.
- [Drive-by Download ML Lab](https://github.com/musaabhasan/driveby-download-ml-lab) - Machine learning research portal for detecting drive-by download attacks and web-based malware delivery.
- [SQL Injection ML Detection Lab](https://github.com/musaabhasan/sqli-ml-detection-lab) - Research portal for SQL injection detection using machine learning and security telemetry.
- [IoT Board SSH Hardening Lab](https://github.com/musaabhasan/iot-board-ssh-hardening-lab) - SSH exposure assessment and hardening portal for IoT development boards and embedded Linux systems.
- [ZigBee WHAS Design Lab](https://github.com/musaabhasan/zigbee-whas-design-lab) - Research portal for designing and evaluating ZigBee wireless home automation systems.
- [Mammogram Fourier Analysis Lab](https://github.com/musaabhasan/mammogram-fourier-analysis-lab) - Medical image-processing research portal based on Fourier transform analysis for mammography.

### Security Culture and Transformation Platforms

- [Human Factors Risk Profiler](https://github.com/musaabhasan/human-factors-risk-profiler) - Human-centered security risk profiling portal for targeted interventions and behavior-aware controls.
- [Security Champion Network Portal](https://github.com/musaabhasan/security-champion-network-portal) - Platform for managing security champion networks, missions, recognition, and measurable impact.
- [Crisis Simulation Command Portal](https://github.com/musaabhasan/crisis-simulation-command-portal) - Cyber crisis simulation planning, scoring, and improvement platform for resilience exercises.
- [Behavioral Security Metrics Portal](https://github.com/musaabhasan/behavioral-security-metrics-portal) - Evidence-based security awareness metrics portal focused on behavior, culture, and intervention outcomes.
- [Security Culture Heatmap Portal](https://github.com/musaabhasan/security-culture-heatmap-portal) - Security culture maturity heatmap for norms, leadership signals, and organizational readiness.
- [Emerging Technology Security Culture Portal](https://github.com/musaabhasan/emerging-technology-security-culture-portal) - Adoption-readiness portal for emerging technology, governance, and security culture alignment.
- [AI Use Case Evaluation Portal](https://github.com/musaabhasan/ai-use-case-evaluation-portal) - Evaluation platform for AI use cases across value, feasibility, data readiness, privacy, ethics, and governance.
- [Transformation Roadmap Portal](https://github.com/musaabhasan/transformation-roadmap-portal) - Roadmap platform for moving security culture programs from compliance orientation to resilience and measurable change.

### Governance, Education, and Secure Enablement

- [Professional Development Registration System Framework](https://github.com/musaabhasan/pdrs-framework) - Secure registration and Moodle enrollment automation framework for professional development programs.
- [Multilingual Certificate Issuer](https://github.com/musaabhasan/multilingual-certificate-issuer) - Arabic/English certificate design, PDF generation, and throttled SMTP distribution platform.
- [AI Security Governance Toolkit](https://github.com/musaabhasan/ai-security-governance-toolkit) - Practical AI security governance controls, templates, evidence registers, playbooks, and policy-as-code examples.

Professional profile and research portfolio: [https://musaab.info](https://musaab.info)
<!-- portfolio:end -->
