CREATE TABLE IF NOT EXISTS paper_references (
  id VARCHAR(64) PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  authors VARCHAR(512) NOT NULL,
  publication_year SMALLINT UNSIGNED NOT NULL,
  venue VARCHAR(255) NOT NULL,
  publisher VARCHAR(160) NOT NULL,
  article_number VARCHAR(32) NOT NULL,
  doi VARCHAR(120) NOT NULL,
  doi_url VARCHAR(255) NOT NULL,
  citation TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS workflow_stages (
  id VARCHAR(64) PRIMARY KEY,
  stage_order TINYINT UNSIGNED NOT NULL,
  name VARCHAR(180) NOT NULL,
  purpose TEXT NOT NULL,
  evidence TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS artifact_sources (
  id VARCHAR(80) PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  evidence_source VARCHAR(160) NOT NULL,
  artifact_path VARCHAR(255) NOT NULL,
  category VARCHAR(120) NOT NULL,
  sensitivity ENUM('Low', 'Medium', 'High') NOT NULL,
  evidentiary_value TEXT NOT NULL,
  validation_strategy TEXT NOT NULL,
  key_fields JSON NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_artifact_category (category),
  INDEX idx_artifact_sensitivity (sensitivity),
  CHECK (JSON_VALID(key_fields))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS forensic_challenges (
  id VARCHAR(80) PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  severity ENUM('Low', 'Medium', 'High') NOT NULL,
  description TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_challenge_severity (severity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS forensic_controls (
  id VARCHAR(80) PRIMARY KEY,
  name VARCHAR(220) NOT NULL,
  category VARCHAR(120) NOT NULL,
  weight TINYINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_control_category (category),
  INDEX idx_control_weight (weight)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS challenge_control_map (
  challenge_id VARCHAR(80) NOT NULL,
  control_id VARCHAR(80) NOT NULL,
  PRIMARY KEY (challenge_id, control_id),
  CONSTRAINT fk_challenge_control_challenge
    FOREIGN KEY (challenge_id) REFERENCES forensic_challenges(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_challenge_control_control
    FOREIGN KEY (control_id) REFERENCES forensic_controls(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS case_assessments (
  id CHAR(36) PRIMARY KEY,
  case_name VARCHAR(180) NOT NULL,
  robot_model VARCHAR(120) NOT NULL,
  acquisition_mode VARCHAR(80) NOT NULL,
  score TINYINT UNSIGNED NOT NULL,
  defensibility VARCHAR(80) NOT NULL,
  risk_tier VARCHAR(40) NOT NULL,
  selected_controls JSON NOT NULL,
  selected_artifacts JSON NOT NULL,
  result_payload JSON NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_case_created (created_at),
  INDEX idx_case_risk (risk_tier),
  CHECK (JSON_VALID(selected_controls)),
  CHECK (JSON_VALID(selected_artifacts)),
  CHECK (JSON_VALID(result_payload))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hash_diff_runs (
  id CHAR(36) PRIMARY KEY,
  case_assessment_id CHAR(36) NULL,
  baseline_manifest JSON NOT NULL,
  final_manifest JSON NOT NULL,
  result_payload JSON NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_hash_case
    FOREIGN KEY (case_assessment_id) REFERENCES case_assessments(id)
    ON DELETE SET NULL,
  CHECK (JSON_VALID(baseline_manifest)),
  CHECK (JSON_VALID(final_manifest)),
  CHECK (JSON_VALID(result_payload))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chain_of_custody_events (
  id CHAR(36) PRIMARY KEY,
  case_assessment_id CHAR(36) NULL,
  event_type VARCHAR(120) NOT NULL,
  actor VARCHAR(160) NOT NULL,
  evidence_reference VARCHAR(220) NOT NULL,
  event_note TEXT NOT NULL,
  event_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_custody_case
    FOREIGN KEY (case_assessment_id) REFERENCES case_assessments(id)
    ON DELETE SET NULL,
  INDEX idx_custody_time (event_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_events (
  id CHAR(36) PRIMARY KEY,
  event_name VARCHAR(120) NOT NULL,
  payload JSON NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_event_name (event_name),
  INDEX idx_audit_created (created_at),
  CHECK (JSON_VALID(payload))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

