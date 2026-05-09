# Robot and IoT Forensic Data Source Triage Workflow

Use this workflow when scoping a humanoid robot, companion app, or IoT-adjacent forensic case. Robot evidence is distributed across the robot, paired mobile devices, cloud services, imported applications, media stores, network context, and user accounts. The triage goal is to identify likely evidence sources quickly while preserving defensibility, privacy, and cross-source validation.

## Case Header

| Field | Value |
| --- | --- |
| Case ID |  |
| Robot model |  |
| Companion devices |  |
| Cloud or account authority |  |
| Suspected activity |  |
| Examiner |  |
| Evidence owner |  |
| Triage date |  |

## Data Source Catalog

| Source | Example evidence | Priority signal | Preservation concern |
| --- | --- | --- | --- |
| Robot system storage | Settings, installed packages, application data, logs, local databases | Robot was directly used or modified | Access may be limited by permissions or root restrictions |
| Companion mobile app | Pairing records, commands, user profile, media references, notifications | User controlled robot from phone or tablet | Device lock state and cloud sync may affect availability |
| Vendor cloud account | Account profile, device list, command history, media sync, support logs | Remote interaction or multi-device control is suspected | Requires authority, account preservation, and provider process |
| Imported applications | APK metadata, app data, permissions, scripts, downloaded content | Third-party app may explain robot behavior | Validate app origin and permissions |
| Voice and interaction artifacts | Voice responses, reminders, routines, assistant logs | Spoken interaction or scheduled action is relevant | Privacy risk for bystanders and household members |
| Media artifacts | Camera images, video, thumbnails, audio, metadata | Visual or audio evidence is in scope | High privacy sensitivity and large storage needs |
| Network context | Wi-Fi profiles, router logs, IP addresses, timestamps | Remote access, timeline, or location context matters | Network logs may be short-lived |
| External services | Calendar, messaging, smart home, learning or care platforms | Robot integrated with other systems | Separate legal or institutional authority may be required |
| Baseline/final hash sets | File changes between acquisition points | Tampering, updates, or app changes are suspected | Keep tool versions and hash method consistent |

## Source Confidence Model

Assign confidence before conclusions are written. A source can be relevant but still weak if it is incomplete, transformed by sync, or unsupported by independent evidence.

| Dimension | High confidence | Medium confidence | Low confidence |
| --- | --- | --- | --- |
| Provenance | Direct export from the robot, companion device, or provider response with chain of custody | Exported through an intermediate tool with documented settings | Screenshot, informal account review, or undocumented extraction |
| Integrity | Hashes recorded before and after handling; unchanged manifests | Hashes recorded for final exports only | No reliable integrity record |
| Completeness | Known scope and artifact coverage; missing areas documented | Partial capture with clear limits | Unknown coverage or unsupported artifact families |
| Temporal reliability | Device, cloud, and examiner time sources reconciled | Timezone known, but clock drift uncertain | Timestamp origin unclear |
| Corroboration | Supported by at least one independent source | Supported by related but dependent data | Single-source assertion only |

## Triage Priority Matrix

| Condition | Priority action |
| --- | --- |
| Robot is powered on and network-connected | Preserve volatile state, document screen/device state, and decide whether to isolate network. |
| Remote wiping or account takeover is possible | Preserve cloud/account evidence and consider network containment. |
| Companion app was used | Acquire companion device/app evidence and correlate with robot artifacts. |
| Imported app is suspected | Prioritize package inventory, app permissions, APK metadata, and app-local data. |
| Media capture is relevant | Preserve media stores, thumbnails, metadata, and privacy minimization plan. |
| Case is court-facing | Require chain of custody, hash manifests, tool versions, peer review, and limitation notes. |
| Household or bystander privacy is high | Apply strict relevance filtering and redaction planning early. |

## Acquisition Planning

| Question | Decision |
| --- | --- |
| Can robot-local logical acquisition capture the needed artifacts? |  |
| Is companion app acquisition required to explain user commands or account state? |  |
| Is cloud preservation necessary before local analysis changes sync state? |  |
| Are root-restricted artifacts central to the case, and is elevated acquisition justified? |  |
| Is a baseline and final hash comparison needed? |  |
| What evidence can be collected without exposing unrelated household data? |  |

## Distributed Evidence Mapping

Use this map to avoid over-relying on a single device. The same event may appear as a robot artifact, a companion-app action, a cloud record, and a network observation.

| Investigative question | Robot-local source | Companion or account source | Independent validation |
| --- | --- | --- | --- |
| Who controlled the robot? | User profiles, pairing records, app permissions | Companion app account, login history, notifications | Cloud account records, router logs, witness timeline |
| What action occurred? | App database, voice response, reminder, media file, system log | Command history, notification text, cached request | Cloud event log, media metadata, network flow timing |
| When did it occur? | Local artifact timestamp and file mtime | Mobile app timestamp and system time | Cloud timestamp, router/DNS logs, external event record |
| Was an imported app involved? | Package list, APK metadata, app-local data | Download history, app store record, mobile cache | APK hash reputation, permission review, sandbox reproduction |
| Was evidence altered? | Baseline/final hash delta, file changes, log gaps | Companion sync changes, account update history | Provider records, backup comparison, acquisition notes |

## Timeline Normalization

Robot and IoT investigations commonly combine device-local clocks, mobile operating system clocks, cloud timestamps, network appliance logs, and examiner workstation time. Normalize before analysis:

- Record timezone, locale, clock source, and observed drift for each source.
- Preserve original timestamps and store normalized timestamps separately.
- Do not collapse repeated events until source-level duplicate behavior is understood.
- Flag timestamps inferred from file metadata differently from application-level event timestamps.
- Treat cloud sync timestamps as evidence of synchronization, not automatically as evidence of user action.

## Validation Controls

| Control | Evidence |
| --- | --- |
| Source separation | Distinguish robot-local, companion-device, cloud, and network-derived facts. |
| Hash manifest | Record SHA-256 hashes for exports, databases, APKs, media, and reports. |
| Timestamp normalization | Record timezone, device time, cloud time, and drift assumptions. |
| Cross-source correlation | Match commands, logs, media, reminders, and app events across sources. |
| Tool repeatability | Capture tool versions and re-run critical parsers where needed. |
| Limitation statement | Explain unavailable root data, missing network identifiers, cloud gaps, and unsupported artifacts. |

## Examiner Decision Log

| Decision | Rationale | Risk accepted | Reviewer |
| --- | --- | --- | --- |
| Network isolation approach |  |  |  |
| Logical, physical, or cloud acquisition scope |  |  |  |
| Root or elevated acquisition decision |  |  |  |
| Companion device inclusion |  |  |  |
| Privacy minimization boundary |  |  |  |
| Parser or tool selection |  |  |  |

## Privacy And Sensitivity Review

| Data category | Handling decision |
| --- | --- |
| Household member profiles |  |
| Voice recordings or transcripts |  |
| Camera images or video |  |
| Location or Wi-Fi context |  |
| Contacts, reminders, or calendar data |  |
| Child, care, health, or education context |  |
| Third-party app data |  |

## Triage Output

| Evidence source | Priority | Acquisition method | Owner | Status | Notes |
| --- | --- | --- | --- | --- | --- |
| Robot local storage |  |  |  |  |  |
| Companion app |  |  |  |  |  |
| Cloud account |  |  |  |  |  |
| Imported applications |  |  |  |  |  |
| Media artifacts |  |  |  |  |  |
| Network context |  |  |  |  |  |

## Escalation Triggers

Escalate when acquisition may alter robot state, root access is required, cloud authority is unclear, media contains highly sensitive bystander data, account compromise is suspected, remote wipe risk is active, or source conflicts affect a key finding.

## Reporting Checklist

- State the acquisition authority, scope, and device/account ownership.
- Separate facts observed directly from inferences drawn through correlation.
- Report source confidence for key findings.
- Include hash manifests, parser versions, acquisition dates, and reviewer notes.
- Disclose unsupported artifacts, inaccessible data, clock limitations, and cloud gaps.
- Redact unrelated household, bystander, child, health, care, or education information.
- Preserve enough detail for another examiner to reproduce the core findings.
