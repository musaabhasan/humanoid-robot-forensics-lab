INSERT INTO paper_references (id, title, authors, publication_year, venue, publisher, article_number, doi, doi_url, citation) VALUES
('iqbal-kazim-macdermott-ikuesan-hasan-marrington-2024', 'Forensic Investigation of Humanoid Social Robot: A Case Study on Zenbo Robot', 'Farkhund Iqbal; Abdullah Kazim; Aine MacDermott; Richard Ikuesan; Musaab Hasan; Andrew Marrington', 2024, 'The 19th International Conference on Availability, Reliability and Security', 'Association for Computing Machinery', '194', '10.1145/3664476.3670906', 'https://doi.org/10.1145/3664476.3670906', 'Iqbal, F., Kazim, A., MacDermott, A., Ikuesan, R., Hasan, M., & Marrington, A. (2024). Forensic Investigation of Humanoid Social Robot: A Case Study on Zenbo Robot. In ARES 2024 - 19th International Conference on Availability, Reliability and Security, Proceedings, Article 194. Association for Computing Machinery. https://doi.org/10.1145/3664476.3670906')
ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO workflow_stages (id, stage_order, name, purpose, evidence) VALUES
('case-authorization', 1, 'Case authorization and scope', 'Confirm authority, scope, device ownership, privacy constraints, and examination boundaries.', 'Signed authorization, scope statement, device identifiers, examiner assignment, and case objectives.'),
('scene-and-device-record', 2, 'Scene and device record', 'Document the humanoid robot, paired devices, network context, power state, and visible interface state.', 'Photographs, notes, serials, robot name, companion devices, network identifiers, and seizure time.'),
('baseline-image', 3, 'Baseline image and hash set', 'Acquire a clean or initial logical image and calculate SHA-256 hashes for future comparison.', 'Initial logical acquisition, file inventory, SHA-256 manifest, acquisition tool version, and examiner notes.'),
('controlled-activity', 4, 'Controlled activity or case activity reconstruction', 'Capture interaction scenarios such as calls, reminders, voice responses, app imports, and media events.', 'Activity matrix, timestamps, companion app actions, robot actions, and expected artifacts.'),
('final-acquisition', 5, 'Final logical acquisition', 'Acquire robot and companion app data using non-destructive logical methods where possible.', 'ADB pull result, ADB backup result, companion app package exports, and second SHA-256 manifest.'),
('hash-differencing', 6, 'Hash differencing', 'Compare initial and final hash manifests to identify added, removed, and modified files.', 'Changed-file report, unchanged-file baseline, modified artifact list, and anomaly notes.'),
('artifact-analysis', 7, 'Artifact analysis and correlation', 'Analyze databases, XML preferences, logs, media, packages, and system configuration artifacts.', 'Artifact worksheets, parsed tables, timeline fragments, cross-source correlation, and confidence ratings.'),
('forensic-reporting', 8, 'Reporting and peer review', 'Produce a repeatable, evidence-backed report with limitations, confidence, and validation notes.', 'Forensic report, timeline, chain-of-custody appendix, limitations, and peer-review record.')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO artifact_sources (id, name, evidence_source, artifact_path, category, sensitivity, evidentiary_value, validation_strategy, key_fields) VALUES
('zenbo-master-videophone-db', 'Zenbo Master videophone database', 'Zenbo Master companion app', 'asusRobotVideophone.db', 'Companion app database', 'High', 'User profile, relationship, linked robot details, calls, notifications, and speak-out command logs.', 'Correlate call and contact records with robot-side asusRobot.db and notification timestamps.', JSON_ARRAY('authority_management_contacts','bind_robot_list','call_logs','notification_center','speak_out_log')),
('zenbo-master-share-pref-offset', 'Zenbo Master server offset preference', 'Zenbo Master companion app', 'share_pref_*.xml', 'XML preference', 'Medium', 'Server and local time offset for timeline normalization.', 'Normalize timestamps and compare with known activities.', JSON_ARRAY('server_time_offset','local_time_reference')),
('zenbo-master-share-pref', 'Zenbo Master shared preference record', 'Zenbo Master companion app', 'SHARE_PREF.xml', 'XML preference', 'High', 'Configured account details and robot name.', 'Correlate configured robot name with app and robot records.', JSON_ARRAY('configured_account','robot_name')),
('zenbo-master-device-status', 'Zenbo Master device status preferences', 'Zenbo Master companion app', 'battery_*.xml, allSpace*, useSpace*, zenboImageVersion*', 'Device status', 'Medium', 'Battery level, image version, storage use, and robot status at last connection.', 'Compare to robot build artifacts and acquisition notes.', JSON_ARRAY('battery_level','storage_total','storage_used','image_version')),
('zenbo-app-builder-phone', 'Zenbo App Builder smartphone package', 'Zenbo App Builder companion app', 'com.asus.appbuilder.editor', 'Companion app package', 'Medium', 'Limited smartphone-side evidentiary value for app import attribution.', 'Record negative findings and seek robot-side correlation.', JSON_ARRAY('package_presence','negative_finding')),
('blockly-engine-db', 'Zenbo App Builder robot database', 'Zenbo robot', 'BlocklyEngine.db', 'Robot database', 'High', 'Imported app properties, sequential IDs, import dates, use dates, versions, and names.', 'Compare highest ID with latest import date and manifest values.', JSON_ARRAY('id','app_id','app_name','app_version','import_date','use_date')),
('zenbo-script-folder', 'Imported app folder collection', 'Zenbo robot', 'ZenboScriptFolderPlace', 'Imported application content', 'High', 'Imported app folders, manifests, block definitions, executable script, icon, media, and bundled files.', 'Compare folder names to BlocklyEngine app_id and inspect bundled files.', JSON_ARRAY('BlocklyManifest.xml','blocksStack.xml','executeScript.html','icon.png')),
('execute-script-html', 'Imported app execution script', 'Zenbo robot', 'executeScript.html', 'Application behavior', 'High', 'Functional behavior of imported apps and potential risky automation logic.', 'Correlate script behavior with blocksStack.xml and observed robot activity.', JSON_ARRAY('script_actions','resource_references')),
('blocks-stack-xml', 'Imported app block stack', 'Zenbo robot', 'blocksStack.xml', 'Application behavior', 'High', 'XML representation of app behavior and traces of earlier versions.', 'Compare with executeScript.html and current manifest.', JSON_ARRAY('block_actions','historical_changes')),
('robot-user-profile-db', 'Robot user and relationship database', 'Zenbo robot', 'robot.asus.com.robotprofileprovider/asusRobot.db', 'Robot database', 'High', 'Users, relatives, profile pictures, birthdays, emails, gender, admin flags, and call logs.', 'Correlate users and calls with Zenbo Master database.', JSON_ARRAY('user_profile','call_logs','relative','admin_flag')),
('robot-reminder-package', 'Robot reminder package', 'Zenbo robot', 'com.asus.robot.reminder', 'Calendar and tasks', 'High', 'Calendar entries and to-do records supporting timeline reconstruction.', 'Compare reminder timestamps with call and response logs.', JSON_ARRAY('calendar_items','todo_items','reminder_times')),
('default-voice-command-logs', 'Default voice command response set', 'Zenbo robot', '/sdcard/Logs/DS/AsrSet*', 'Voice response reference', 'Low', 'Default command and response mappings that clarify capability.', 'Confirm baseline presence and avoid over-attribution.', JSON_ARRAY('asr_set_file','default_command')),
('created-voice-response-logs', 'Created voice response logs', 'Zenbo robot', '/storage/self/primary/Logs/DS/Record*TtsInfo.txt', 'Voice response evidence', 'High', 'Daily files containing Zenbo responses to user voice commands.', 'Correlate file date with other activity records and note missing per-response timestamps.', JSON_ARRAY('file_date','response_text')),
('storytelling-media', 'Storytelling content', 'Zenbo robot', '/system/media/storytelling', 'Media and scripted content', 'Medium', 'Stories, images, audio, video, and scripts that may reveal modified behavior.', 'Compare content against baseline and review script references.', JSON_ARRAY('story_assets','javascript_files','media_metadata')),
('system-app-directories', 'System app directories', 'Zenbo robot', '/system/app and /system/priv-app', 'System packages', 'Medium', 'APK inventory for core system and privileged applications.', 'Compare APK hashes with baseline and installed package records.', JSON_ARRAY('apk_path','package_name','hash')),
('frosting-db', 'Installed package listing', 'Zenbo robot', 'com.android.vending/frosting.db', 'Package inventory', 'Medium', 'Installed packages and actual installation paths.', 'Correlate with system app directories and APK metadata.', JSON_ARRAY('package_name','installed_path')),
('build-prop', 'Build properties', 'Zenbo robot', 'build.prop', 'System configuration', 'Low', 'Android version, ASUS version, preferred network type, and last security patch.', 'Compare to acquisition notes and package inventory.', JSON_ARRAY('android_version','asus_version','security_patch')),
('cpu-accounting', 'CPU accounting files', 'Zenbo robot', '/acct/cpuacct.*', 'Runtime activity', 'Medium', 'CPU uptime and processing details for timing validation.', 'Compare with kernel logs and acquisition timestamps.', JSON_ARRAY('cpu_usage','cpu_stat')),
('kernel-and-diagnostic-logs', 'Kernel and diagnostic logs', 'Zenbo robot', '/sdcard/Logs/last_kernel_logs and related logs', 'System logs', 'High', 'Kernel, diagnostic, event, and server-debugging logs.', 'Normalize timestamps and correlate with app-level records.', JSON_ARRAY('kernel_message','event_tag','timestamp')),
('dcim-download-media', 'Robot media directories', 'Zenbo robot', 'DCIM and Download', 'Media evidence', 'High', 'Images, videos, and downloaded files stored on or captured by the robot.', 'Analyze metadata, camera profile compatibility, hashes, and timeline context.', JSON_ARRAY('filename','media_type','metadata','hash')),
('camera-profiles', 'Camera configuration profiles', 'Zenbo robot', '/system/etc/camera*.xml and camera_ddr.sh', 'Device capability', 'Low', 'Camera resolution and image-size details for attribution or exclusion.', 'Compare image metadata to supported camera profile values.', JSON_ARRAY('resolution','camera_profile')),
('audio-profiles', 'Audio configuration profiles', 'Zenbo robot', '/system/etc/audio_*.conf', 'Device capability', 'Low', 'Audio policy and effect configuration for capability analysis.', 'Compare audio evidence and system behavior with audio policies.', JSON_ARRAY('audio_policy','effect_profile')),
('recent-documents-db', 'Recent documents database', 'Zenbo robot', 'com.android.documentsui/recent.db', 'Usage timeline', 'Medium', 'Timestamps for app or document use.', 'Correlate with reminders, packages, and logs.', JSON_ARRAY('recent_item','last_used_time')),
('photos-databases', 'Photo app databases', 'Zenbo robot', 'com.google.android.apps.photos/gphotos0.db and local_trash.db', 'Media evidence', 'High', 'Photo storage locations and deleted photo references.', 'Correlate with DCIM, Download, metadata, and deletion timeline.', JSON_ARRAY('photo_path','trash_path','deleted_indicator')),
('settings-provider', 'Android settings provider', 'Zenbo robot', 'com.android.providers.settings', 'System configuration', 'Medium', 'Screen brightness, auto-time, notification, Wi-Fi, and USB settings.', 'Compare with acquisition context, network state, and timeline.', JSON_ARRAY('brightness','auto_time','wifi_notification','usb_mass_storage'))
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO forensic_challenges (id, name, severity, description) VALUES
('distributed-evidence', 'Distributed evidence across robot, companion app, and network context', 'High', 'Full IoT forensic trails require piecing together artifacts from multiple locations.'),
('logical-only-limits', 'Logical acquisition limitations', 'High', 'Non-intrusive acquisition preserves integrity but may not expose deleted content or protected directories directly.'),
('root-restricted-data', 'Root-restricted application data', 'High', 'ADB pull cannot extract protected application data directly, requiring alternative logical methods.'),
('timestamp-friction', 'Timestamp normalization and missing timestamps', 'Medium', 'Server offsets, local time, extraction time, and missing timestamps require careful interpretation.'),
('negative-network-traces', 'Absent network identifiers', 'Medium', 'Some app and robot packages may not expose source IP addresses or smartphone identifiers.'),
('sensitive-personal-data', 'Sensitive personal and household data', 'High', 'Robots may hold user profiles, relatives, media, reminders, and interaction history.'),
('artifact-tampering', 'Artifact tampering and version mismatch', 'High', 'Imported app IDs, dates, manifests, scripts, and block stacks must be compared for consistency.'),
('tool-repeatability', 'Tool repeatability and forensic defensibility', 'High', 'Established tools and repeatable hash comparison support defensible results.')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO forensic_controls (id, name, category, weight) VALUES
('case-authorization', 'Document case authority and scope', 'Governance', 8),
('chain-of-custody', 'Maintain chain-of-custody events', 'Evidence handling', 9),
('scene-documentation', 'Capture scene, device, and network context', 'Evidence handling', 7),
('multi-source-inventory', 'Inventory robot, smartphone, app, and network evidence sources', 'Scoping', 9),
('non-intrusive-first', 'Prioritize non-intrusive acquisition methods', 'Acquisition', 8),
('adb-method-record', 'Record ADB pull and backup method details', 'Acquisition', 8),
('adb-backup-workflow', 'Use logical backup workflow for protected app data where available', 'Acquisition', 7),
('baseline-hash-set', 'Create baseline SHA-256 hash manifest', 'Integrity', 10),
('final-hash-set', 'Create final SHA-256 hash manifest', 'Integrity', 10),
('hash-differencing', 'Compare baseline and final hashes to isolate changed artifacts', 'Integrity', 10),
('tool-version-record', 'Record forensic tool versions and settings', 'Validation', 7),
('multi-tool-validation', 'Validate findings across more than one forensic tool where practical', 'Validation', 8),
('tool-output-validation', 'Validate tool output against manual artifact review', 'Validation', 7),
('artifact-prioritization', 'Prioritize high-value artifact sources before deep review', 'Analysis', 7),
('cross-source-correlation', 'Correlate robot-side and companion-app artifacts', 'Analysis', 9),
('timeline-normalization', 'Normalize timestamps across device, server offset, and acquisition time', 'Timeline', 9),
('server-offset-review', 'Review server/local time offset preferences', 'Timeline', 6),
('manifest-consistency-check', 'Compare imported app database, manifest, script, and folder values', 'Application analysis', 9),
('script-behavior-review', 'Review imported app scripts and block stacks for risky behavior', 'Application analysis', 9),
('media-metadata-review', 'Analyze media metadata and camera profile compatibility', 'Media analysis', 7),
('negative-finding-record', 'Document negative findings and attribution limits', 'Reporting', 6),
('network-context-note', 'Document network context even when package records lack IP traces', 'Reporting', 5),
('privacy-minimization', 'Minimize exposure of personal and household data', 'Privacy', 8),
('evidence-encryption', 'Encrypt evidence storage and exports', 'Privacy', 8),
('role-based-review', 'Restrict sensitive artifact review by role', 'Privacy', 6),
('limitations-register', 'Maintain a limitations and assumptions register', 'Reporting', 7),
('confidence-rating', 'Assign confidence ratings to findings', 'Reporting', 7),
('peer-review', 'Perform independent review before report release', 'Reporting', 8)
ON DUPLICATE KEY UPDATE name = VALUES(name), weight = VALUES(weight);

INSERT INTO challenge_control_map (challenge_id, control_id) VALUES
('distributed-evidence', 'multi-source-inventory'),
('distributed-evidence', 'cross-source-correlation'),
('distributed-evidence', 'timeline-normalization'),
('logical-only-limits', 'non-intrusive-first'),
('logical-only-limits', 'adb-method-record'),
('logical-only-limits', 'limitations-register'),
('root-restricted-data', 'adb-backup-workflow'),
('root-restricted-data', 'artifact-prioritization'),
('root-restricted-data', 'tool-output-validation'),
('timestamp-friction', 'timeline-normalization'),
('timestamp-friction', 'server-offset-review'),
('timestamp-friction', 'confidence-rating'),
('negative-network-traces', 'negative-finding-record'),
('negative-network-traces', 'cross-source-correlation'),
('negative-network-traces', 'network-context-note'),
('sensitive-personal-data', 'privacy-minimization'),
('sensitive-personal-data', 'evidence-encryption'),
('sensitive-personal-data', 'role-based-review'),
('artifact-tampering', 'hash-differencing'),
('artifact-tampering', 'manifest-consistency-check'),
('artifact-tampering', 'script-behavior-review'),
('tool-repeatability', 'tool-version-record'),
('tool-repeatability', 'multi-tool-validation'),
('tool-repeatability', 'peer-review')
ON DUPLICATE KEY UPDATE challenge_id = VALUES(challenge_id);

