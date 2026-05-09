# Robot Voice And Media Privacy Minimization Workflow

Use this workflow when humanoid robot evidence includes voice responses, recorded audio, camera captures, thumbnails, video, household context, reminders, or companion-app media references. Robot and IoT evidence often captures people who are not subjects of the investigation, so privacy minimization must be planned before broad extraction, review, export, or reporting.

## Review Header

| Field | Value |
| --- | --- |
| Case ID |  |
| Robot model |  |
| Acquisition source | Robot / Companion app / Cloud account / Network / Hybrid |
| Voice or media source |  |
| Examiner |  |
| Privacy reviewer |  |
| Legal or institutional authority |  |
| Review date |  |

## Scope Decision

| Question | Decision |
| --- | --- |
| What specific allegation, incident, or activity makes voice or media evidence relevant? |  |
| Which date, time, location, user, application, or event boundary limits review? |  |
| Is the evidence needed for identity, timeline, user command, environmental context, or app behavior? |  |
| Can the same fact be proven with lower-sensitivity metadata or logs? |  |
| Are household members, visitors, children, care recipients, students, or bystanders likely to appear? |  |
| Is cloud preservation required before local handling changes sync state? |  |

## Data Categories

| Category | Examples | Sensitivity | Minimization Action |
| --- | --- | --- | --- |
| Voice interaction metadata | Command time, response ID, routine trigger | Medium | Prefer metadata where content is not necessary |
| Voice recordings or transcripts | Spoken command, assistant response, ambient speech | High | Review only scoped windows and redact unrelated speech |
| Camera images | Captured photos, still frames, thumbnails | High | Filter by relevance, blur unrelated people or spaces |
| Video files | Motion clips, interaction recordings | High | Segment relevant portions and restrict raw files |
| Audio files | Microphone captures, media messages | High | Use transcript excerpts only when content is necessary |
| Household context | Rooms, routines, family members, schedules | High | Remove unrelated context from reports and exhibits |
| Companion-app media references | Cached thumbnails, sync records, shared media | Medium to high | Separate reference metadata from full media content |
| Cloud media events | Upload, sync, share, delete, access events | Medium | Use event records when media content is unnecessary |

## Collection Boundaries

| Boundary | Required Decision |
| --- | --- |
| Date and time range |  |
| Robot account or user profile |  |
| Companion device or app account |  |
| Cloud service or vendor export |  |
| Media type in scope | Audio / Image / Video / Thumbnail / Metadata |
| Raw content required? | Yes / No / Conditional |
| Redaction or masking needed? | Yes / No |
| Reviewer access level | Examiner / Peer reviewer / Legal / Support / Other |

## Privacy-Preserving Review Steps

1. Start with metadata, hashes, timestamps, and event records before opening raw media.
2. Confirm that the source falls within the authority and scope boundary.
3. Create a working copy and hash it before review.
4. Filter by approved date, profile, application, and event boundaries.
5. Record why raw content was needed if metadata was insufficient.
6. Redact unrelated faces, voices, rooms, names, screens, documents, and identifiers.
7. Keep raw media in restricted evidence storage; use minimized derivatives for reports.
8. Record each export, transcript, clip, or screenshot as a derived evidence item.

## Redaction Decision Table

| Content | Default Handling | Exception |
| --- | --- | --- |
| Bystander face | Blur or crop | Identity is directly relevant and authorized |
| Bystander voice | Omit or mask transcript | Speaker identity or words are directly relevant |
| Child or care-recipient content | Exclude or heavily redact | Explicit authority and necessity are documented |
| Household room details | Crop, mask, or describe generally | Room layout is relevant to timeline or event reconstruction |
| Screens, mail, documents, IDs | Mask text and identifiers | Specific text is in scope and authorized |
| Unrelated media | Exclude from report | Needed to explain artifact sequence or tool limitation |
| Companion-app contacts | Mask unrelated names and identifiers | Contact is directly tied to the event |

## Derived Evidence Register

| Derived Item | Source Item | Transformation | Hash | Reviewer | Privacy Action |
| --- | --- | --- | --- | --- | --- |
|  |  | Transcript excerpt |  |  |  |
|  |  | Cropped image |  |  |  |
|  |  | Redacted frame |  |  |  |
|  |  | Timeline metadata row |  |  |  |

## Reporting Rules

- Report the least sensitive evidence that proves the point.
- Separate metadata findings from raw content findings.
- Quote or transcribe only the relevant portion of voice evidence.
- Use still frames or thumbnails only when necessary and redacted.
- Document that unrelated bystander, household, child, care, health, education, or visitor content was minimized.
- State limitations when redaction prevents full context from being shown in the report.
- Keep raw files, redacted derivatives, hashes, and reviewer decisions linked in the evidence register.

## Escalation Triggers

Escalate to a privacy reviewer, legal authority, or case owner when:

- raw media includes children, care recipients, students, health context, or intimate household details,
- the scope expands beyond the original incident window,
- cloud media requires provider preservation or cross-account access,
- a bystander becomes relevant to a finding,
- redaction may remove context needed for expert testimony,
- a support request asks for raw media outside the evidence team,
- media appears manipulated, deleted, or generated by an imported application.

## Closure Checklist

- Scope, authority, and media categories are documented.
- Metadata-first review was attempted where appropriate.
- Raw content review is justified and limited.
- Derived evidence items are hashed and registered.
- Redaction decisions are documented.
- Access to raw voice and media content is restricted.
- Report exhibits use minimized derivatives where possible.
- Limitations and privacy handling are stated in the final report.
