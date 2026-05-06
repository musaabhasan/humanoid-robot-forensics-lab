# Paper Alignment

This repository is based on:

Iqbal, F., Kazim, A., MacDermott, A., Ikuesan, R., Hasan, M., & Marrington, A. (2024). **Forensic Investigation of Humanoid Social Robot: A Case Study on Zenbo Robot**. In *ARES 2024 - 19th International Conference on Availability, Reliability and Security, Proceedings*, Article 194. Association for Computing Machinery. https://doi.org/10.1145/3664476.3670906

## Concepts Implemented

| Paper concept | Repository implementation |
| --- | --- |
| Zenbo as a potential digital witness | Case assessment focused on humanoid robot evidence relevance. |
| Data generation, acquisition, analysis, and reporting | Eight-stage forensic workflow model. |
| Logical acquisition through ADB pull and backup | Acquisition controls and case context. |
| SHA-256 comparison of initial and final images | Hash-differencing API and workflow. |
| Zenbo Master database artifacts | Artifact entries for `asusRobotVideophone.db` and related preferences. |
| Zenbo App Builder artifacts | `BlocklyEngine.db`, app folders, manifests, scripts, and block-stack artifacts. |
| Robot user, relationship, and call records | Robot profile database artifact model. |
| Voice response and storytelling artifacts | Voice response logs and storytelling media artifact entries. |
| System apps, build properties, logs, media, CPU accounting | Android artifact categories and validation strategies. |
| Missing IP or smartphone attribution data | Negative-finding and network-context controls. |
| Privacy-sensitive personal data | Privacy minimization, encrypted storage, and role-based review controls. |

## Deliberate Boundaries

The repository does not provide device exploitation tooling. It models authorized forensic casework, artifact triage, logical-acquisition documentation, hash comparison, and evidence reporting practices derived from the research.

