<?php

declare(strict_types=1);

namespace RobotForensicsLab\Repository;

use PDO;
use Throwable;
use RobotForensicsLab\Support\Uuid;

final class LabRepository
{
    /**
     * @param array<string, mixed> $catalog
     */
    public function __construct(
        private readonly array $catalog,
        private readonly ?PDO $pdo = null
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function paper(): array
    {
        return $this->catalog['paper'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function workflowStages(): array
    {
        return $this->catalog['workflow_stages'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function artifactSources(): array
    {
        return $this->catalog['artifact_sources'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forensicChallenges(): array
    {
        return $this->catalog['forensic_challenges'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forensicControls(): array
    {
        return $this->catalog['forensic_controls'];
    }

    /**
     * @return array<int, string>
     */
    public function reportDimensions(): array
    {
        return $this->catalog['report_dimensions'];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function controlsById(): array
    {
        $indexed = [];
        foreach ($this->forensicControls() as $control) {
            $indexed[(string) $control['id']] = $control;
        }

        return $indexed;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function artifactsById(): array
    {
        $indexed = [];
        foreach ($this->artifactSources() as $artifact) {
            $indexed[(string) $artifact['id']] = $artifact;
        }

        return $indexed;
    }

    /**
     * @param array<string, mixed> $assessment
     */
    public function saveCaseAssessment(array $assessment): ?string
    {
        if ($this->pdo === null) {
            return null;
        }

        $id = Uuid::v4();

        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO case_assessments (
                    id,
                    case_name,
                    robot_model,
                    acquisition_mode,
                    score,
                    defensibility,
                    risk_tier,
                    selected_controls,
                    selected_artifacts,
                    result_payload,
                    created_at
                ) VALUES (
                    :id,
                    :case_name,
                    :robot_model,
                    :acquisition_mode,
                    :score,
                    :defensibility,
                    :risk_tier,
                    :selected_controls,
                    :selected_artifacts,
                    :result_payload,
                    NOW()
                )'
            );
            $context = $assessment['context'];
            $statement->execute([
                'id' => $id,
                'case_name' => (string) ($context['case_name'] ?? 'Robot forensic case'),
                'robot_model' => (string) ($context['robot_model'] ?? 'Zenbo'),
                'acquisition_mode' => (string) ($context['acquisition_mode'] ?? 'logical'),
                'score' => (int) $assessment['score'],
                'defensibility' => (string) $assessment['defensibility'],
                'risk_tier' => (string) $assessment['risk_tier'],
                'selected_controls' => json_encode($assessment['selected_controls'], JSON_THROW_ON_ERROR),
                'selected_artifacts' => json_encode($assessment['selected_artifacts'], JSON_THROW_ON_ERROR),
                'result_payload' => json_encode($assessment, JSON_THROW_ON_ERROR),
            ]);

            $this->audit('case_assessment.created', ['assessment_id' => $id, 'score' => $assessment['score']]);

            return $id;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recentCaseAssessments(int $limit = 5): array
    {
        if ($this->pdo === null) {
            return [];
        }

        try {
            $statement = $this->pdo->prepare(
                'SELECT id, case_name, robot_model, score, defensibility, risk_tier, created_at
                 FROM case_assessments
                 ORDER BY created_at DESC
                 LIMIT :limit'
            );
            $statement->bindValue('limit', max(1, min(25, $limit)), PDO::PARAM_INT);
            $statement->execute();

            return $statement->fetchAll();
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function audit(string $event, array $payload): void
    {
        if ($this->pdo === null) {
            return;
        }

        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO audit_events (id, event_name, payload, created_at)
                 VALUES (:id, :event_name, :payload, NOW())'
            );
            $statement->execute([
                'id' => Uuid::v4(),
                'event_name' => $event,
                'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            ]);
        } catch (Throwable) {
        }
    }
}

