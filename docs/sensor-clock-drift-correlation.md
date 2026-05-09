# Robot Sensor Clock Drift Correlation Workflow

Humanoid robot investigations often combine robot databases, Android companion apps, cloud records, media files, reminders, voice interactions, motion or sensor logs, and network evidence. These sources rarely share one perfectly synchronized clock. Use this workflow to document time-source confidence, estimate drift, and avoid overstating event order in reports.

## Objectives

- Identify every time source used in the case.
- Preserve original timestamps and timezone assumptions.
- Estimate drift between robot, companion app, cloud, network, and examiner reference time.
- Correlate events across sensors and records with explicit confidence.
- Record limitations before building a court-facing or audit-facing timeline.

## Time Source Inventory

| Source | Example Evidence | Timestamp Semantics | Timezone or Offset | Confidence |
| --- | --- | --- | --- | --- |
| Robot system clock | System logs, Android settings, boot records | Local system event time |  | High / medium / low |
| Robot application database | Zenbo Master, reminders, call logs, media references | App-created, modified, synced, or deleted time |  | High / medium / low |
| Companion mobile app | App database, notifications, cache, account settings | Phone-side interaction or sync event |  | High / medium / low |
| Cloud service | Account export, server logs, API response, support return | Server-observed or account-event time |  | High / medium / low |
| Media metadata | EXIF, video container, audio file metadata | Capture, encode, modify, or file-system time |  | High / medium / low |
| Network evidence | Router logs, DHCP, DNS, proxy, packet capture | Network-observed connection or resolution time |  | High / medium / low |
| Examiner reference | Acquisition workstation, NTP check, custody event | Collection or observation time | UTC reference | High / medium / low |

## Drift Measurement Procedure

1. Record examiner workstation time, timezone, and NTP status before acquisition.
2. Record robot displayed time and configured timezone when available.
3. Capture companion phone time and timezone when the phone is in scope.
4. Identify clear anchors, such as login, Wi-Fi association, media capture, call start, reminder trigger, app install, or cloud sync.
5. Compare each source timestamp against the closest reliable anchor.
6. Estimate drift direction and magnitude for each source.
7. Preserve both original time and normalized UTC time in the working timeline.
8. Recalculate event order only after drift estimates are documented.
9. Mark any event whose order changes after drift adjustment.

## Anchor Selection

| Anchor Type | Useful When | Caution |
| --- | --- | --- |
| Acquisition start and end | Establishing examiner reference and custody window | Does not prove when user actions occurred |
| Network connection event | Matching device presence to router or DHCP logs | Router clocks may also drift |
| Media capture | Correlating camera, filesystem, and app records | Metadata may be edited or generated at export time |
| Cloud sync | Linking local state to account/server record | Sync time is not always creation time |
| Reminder or scheduled event | Testing expected user-facing trigger time | Reminder database may store local time or UTC |
| Voice interaction | Correlating audio, transcript, app log, and cloud event | Speech pipelines may create multiple processing timestamps |
| App install or update | Aligning package state and usage windows | Store logs and local package timestamps may differ |

## Correlation Confidence

| Confidence | Criteria | Reporting Language |
| --- | --- | --- |
| High | Multiple independent sources align after documented drift correction, and timestamp semantics are known | The timeline strongly supports |
| Medium | Two sources align, but one timestamp meaning or clock source has a limitation | The timeline supports, with timing limitations |
| Low | Single source, unclear timestamp semantics, unverified clock, or possible export-time overwrite | The record indicates, but timing is uncertain |
| Inconclusive | Sources conflict or drift cannot be estimated | The evidence is insufficient to determine sequence |

Assign confidence per event or event pair, not globally. A robot reminder trigger may have high confidence while a media file modification time in the same case has low confidence.

## Drift and Event Table

| Event ID | Source | Original Timestamp | Original Timezone | Normalized UTC | Drift Estimate | Anchor Used | Confidence | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| EVT-001 |  |  |  |  |  |  | High / medium / low / inconclusive |  |

## Common Timing Pitfalls

| Pitfall | Risk | Mitigation |
| --- | --- | --- |
| Treating cloud sync time as local creation time | False event ordering | Label sync, upload, server-observed, and created times separately |
| Trusting router or IoT hub time without validation | Correlation drift | Record router clock and compare with NTP or known events |
| Ignoring companion app timezone | Shifted timeline | Preserve phone timezone and normalize separately |
| Using media file modified time as capture time | Misattribution after copy/export | Compare EXIF, container metadata, app references, and filesystem metadata |
| Assuming database fields use one timezone | Mixed UTC and local values | Identify field semantics from schema, app behavior, or controlled testing |
| Reporting exact order where events overlap within drift range | Overstated precision | Use ranges and confidence language |

## Privacy and Scope Controls

- Minimize bystander voice, image, household, and contact data when building timing anchors.
- Redact unrelated family, visitor, and household context from timeline exhibits unless directly relevant.
- Keep raw media and transcript timing evidence in restricted evidence storage.
- Use derived timing tables when peer review or reporting does not require raw content.
- Document when privacy minimization limits event reconstruction.

## Report-Ready Statements

Use calibrated statements:

- "The robot database event and companion-app notification align within the documented drift range."
- "The cloud sync timestamp confirms account-side observation, not necessarily local robot creation."
- "The media metadata supports a capture window, but export metadata prevents exact ordering."
- "The event order should be treated as approximate because router and robot clocks were not independently synchronized."

Avoid statements such as:

- "This timestamp proves the robot performed the action at exactly this second" unless source semantics and drift are strongly validated.
- "The cloud timestamp proves the seized robot created the record" without device binding and local corroboration.

## Closure Checklist

| Check | Pass Criteria |
| --- | --- |
| Time-source inventory complete | Every timeline source has timestamp semantics and timezone assumptions |
| Drift estimate documented | Direction, magnitude, anchor, and confidence are recorded |
| Original timestamps preserved | UTC normalization does not replace original values |
| Event order limitations recorded | Ambiguous ordering is stated clearly |
| Privacy minimization applied | Raw voice, media, and household data are scoped and redacted where possible |
| Peer review completed | Another reviewer can reproduce drift reasoning from the evidence table |
