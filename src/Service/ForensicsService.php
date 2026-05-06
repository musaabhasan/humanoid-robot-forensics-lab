<?php

declare(strict_types=1);

namespace RobotForensicsLab\Service;

use RobotForensicsLab\Repository\LabRepository;

final class ForensicsService
{
    public function __construct(private readonly LabRepository $repository)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $artifactCategories = [];
        $controlCategories = [];

        foreach ($this->repository->artifactSources() as $artifact) {
            $category = (string) $artifact['category'];
            $artifactCategories[$category] = ($artifactCategories[$category] ?? 0) + 1;
        }

        foreach ($this->repository->forensicControls() as $control) {
            $category = (string) $control['category'];
            $controlCategories[$category] = ($controlCategories[$category] ?? 0) + 1;
        }

        return [
            'paper' => $this->repository->paper(),
            'metrics' => [
                'workflow_stages' => count($this->repository->workflowStages()),
                'artifact_sources' => count($this->repository->artifactSources()),
                'forensic_challenges' => count($this->repository->forensicChallenges()),
                'forensic_controls' => count($this->repository->forensicControls()),
                'maximum_score' => $this->maximumScore(),
            ],
            'artifact_categories' => $artifactCategories,
            'control_categories' => $controlCategories,
            'report_dimensions' => $this->repository->reportDimensions(),
            'recent_assessments' => $this->repository->recentCaseAssessments(),
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function assessCase(array $input): array
    {
        $context = $this->context($input);
        $selectedControls = $this->normalizeIds($input['controls'] ?? []);
        $selectedArtifacts = $this->normalizeIds($input['artifacts'] ?? []);
        $controlsById = $this->repository->controlsById();
        $artifactsById = $this->repository->artifactsById();
        $selectedControls = array_values(array_intersect($selectedControls, array_keys($controlsById)));
        $selectedArtifacts = array_values(array_intersect($selectedArtifacts, array_keys($artifactsById)));

        $earned = 0;
        foreach ($selectedControls as $controlId) {
            $earned += (int) $controlsById[$controlId]['weight'];
        }

        $coverageBonus = min(12, (int) floor(count($selectedArtifacts) / 3));
        $baseScore = (int) round(($earned / $this->maximumScore()) * 100);
        $score = max(0, min(100, $baseScore + $coverageBonus - $this->contextPenalty($context)));
        $challengeProfile = $this->challengeProfile($selectedControls, $selectedArtifacts, $context, $baseScore);
        $riskTier = $this->riskTier($challengeProfile);
        $defensibility = $this->defensibility($score, $riskTier);
        $artifactPlan = $this->artifactPlan($selectedArtifacts, $context);
        $recommendations = $this->recommendations($selectedControls, $challengeProfile);

        $result = [
            'score' => $score,
            'base_score' => $baseScore,
            'available_weight' => $this->maximumScore(),
            'defensibility' => $defensibility,
            'risk_tier' => $riskTier,
            'selected_controls' => $selectedControls,
            'selected_artifacts' => $selectedArtifacts,
            'context' => $context,
            'challenge_profile' => $challengeProfile,
            'artifact_plan' => $artifactPlan,
            'recommendations' => $recommendations,
            'report_outline' => $this->reportOutline($context, $artifactPlan),
            'next_actions' => $this->nextActions($defensibility, $recommendations),
        ];

        $assessmentId = $this->repository->saveCaseAssessment($result);
        if ($assessmentId !== null) {
            $result['assessment_id'] = $assessmentId;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function hashDiff(array $input): array
    {
        $baseline = $this->normalizeManifest($input['baseline'] ?? []);
        $final = $this->normalizeManifest($input['final'] ?? []);

        $added = [];
        $removed = [];
        $modified = [];
        $unchanged = [];

        foreach ($final as $path => $hash) {
            if (!array_key_exists($path, $baseline)) {
                $added[$path] = $hash;
                continue;
            }

            if (hash_equals($baseline[$path], $hash)) {
                $unchanged[$path] = $hash;
            } else {
                $modified[$path] = [
                    'baseline' => $baseline[$path],
                    'final' => $hash,
                ];
            }
        }

        foreach ($baseline as $path => $hash) {
            if (!array_key_exists($path, $final)) {
                $removed[$path] = $hash;
            }
        }

        return [
            'summary' => [
                'baseline_files' => count($baseline),
                'final_files' => count($final),
                'added' => count($added),
                'removed' => count($removed),
                'modified' => count($modified),
                'unchanged' => count($unchanged),
            ],
            'added' => $added,
            'removed' => $removed,
            'modified' => $modified,
            'unchanged' => $unchanged,
            'integrity_notes' => $this->hashDiffNotes($added, $removed, $modified),
        ];
    }

    private function maximumScore(): int
    {
        return array_sum(array_map(
            static fn (array $control): int => (int) $control['weight'],
            $this->repository->forensicControls()
        ));
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function context(array $input): array
    {
        return [
            'case_name' => $this->cleanText($input['case_name'] ?? 'Humanoid robot forensic case'),
            'robot_model' => $this->cleanText($input['robot_model'] ?? 'Zenbo Robot'),
            'acquisition_mode' => $this->cleanChoice($input['acquisition_mode'] ?? 'logical', ['logical', 'logical-plus-companion', 'physical-review', 'unknown'], 'logical'),
            'paired_smartphone' => $this->bool($input['paired_smartphone'] ?? true),
            'remote_interactions' => $this->bool($input['remote_interactions'] ?? true),
            'suspected_imported_app' => $this->bool($input['suspected_imported_app'] ?? false),
            'deleted_data_suspected' => $this->bool($input['deleted_data_suspected'] ?? false),
            'privacy_sensitive' => $this->bool($input['privacy_sensitive'] ?? true),
            'network_context_available' => $this->bool($input['network_context_available'] ?? false),
            'court_facing_report' => $this->bool($input['court_facing_report'] ?? true),
        ];
    }

    private function cleanText(mixed $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return substr($value, 0, 150);
    }

    /**
     * @param array<int, string> $allowed
     */
    private function cleanChoice(mixed $value, array $allowed, string $default): string
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function bool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * @param mixed $ids
     * @return array<int, string>
     */
    private function normalizeIds(mixed $ids): array
    {
        if (is_string($ids)) {
            $ids = array_filter(array_map('trim', explode(',', $ids)));
        }

        if (!is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $id): string => preg_replace('/[^a-z0-9-]/', '', strtolower((string) $id)) ?? '',
            $ids
        ))));
    }

    /**
     * @param array<string, mixed> $context
     */
    private function contextPenalty(array $context): int
    {
        $penalty = 0;
        $penalty += $context['deleted_data_suspected'] ? 7 : 0;
        $penalty += $context['privacy_sensitive'] ? 3 : 0;
        $penalty += $context['court_facing_report'] ? 4 : 0;
        $penalty += !$context['network_context_available'] && $context['remote_interactions'] ? 4 : 0;
        $penalty += $context['acquisition_mode'] === 'unknown' ? 10 : 0;

        return $penalty;
    }

    /**
     * @param array<int, string> $selectedControls
     * @param array<int, string> $selectedArtifacts
     * @param array<string, mixed> $context
     * @return array<int, array<string, mixed>>
     */
    private function challengeProfile(array $selectedControls, array $selectedArtifacts, array $context, int $baseScore): array
    {
        $severityBase = ['High' => 82, 'Medium' => 58, 'Low' => 35];
        $artifactCoverage = min(18, (int) floor(count($selectedArtifacts) / 2));
        $profile = [];

        foreach ($this->repository->forensicChallenges() as $challenge) {
            $required = array_map('strval', $challenge['controls']);
            $covered = count(array_intersect($required, $selectedControls));
            $residual = ($severityBase[(string) $challenge['severity']] ?? 60)
                - ($covered * 20)
                - min(12, (int) round($baseScore * 0.12))
                - $artifactCoverage;

            $id = (string) $challenge['id'];
            if ($id === 'root-restricted-data' && $context['acquisition_mode'] === 'logical') {
                $residual += 12;
            }
            if ($id === 'logical-only-limits' && $context['deleted_data_suspected']) {
                $residual += 18;
            }
            if ($id === 'artifact-tampering' && $context['suspected_imported_app']) {
                $residual += 18;
            }
            if ($id === 'negative-network-traces' && $context['remote_interactions'] && !$context['network_context_available']) {
                $residual += 14;
            }
            if ($id === 'sensitive-personal-data' && $context['privacy_sensitive']) {
                $residual += 10;
            }
            if ($id === 'tool-repeatability' && $context['court_facing_report']) {
                $residual += 10;
            }

            $residual = max(5, min(100, $residual));
            $profile[] = [
                'id' => $id,
                'name' => (string) $challenge['name'],
                'severity' => (string) $challenge['severity'],
                'residual_score' => $residual,
                'residual_tier' => $this->tierFromScore($residual),
                'covered_controls' => $covered,
                'required_controls' => count($required),
                'description' => (string) $challenge['description'],
                'controls' => $required,
            ];
        }

        usort($profile, static fn (array $left, array $right): int => $right['residual_score'] <=> $left['residual_score']);

        return $profile;
    }

    /**
     * @param array<int, array<string, mixed>> $profile
     */
    private function riskTier(array $profile): string
    {
        return $this->tierFromScore((int) max(array_column($profile, 'residual_score')));
    }

    private function tierFromScore(int $score): string
    {
        return match (true) {
            $score >= 85 => 'Critical',
            $score >= 70 => 'High',
            $score >= 45 => 'Medium',
            default => 'Low',
        };
    }

    private function defensibility(int $score, string $riskTier): string
    {
        if ($score >= 90 && $riskTier === 'Low') {
            return 'Court Ready';
        }
        if ($score >= 72 && in_array($riskTier, ['Low', 'Medium'], true)) {
            return 'Lab Defensible';
        }
        if ($score >= 45) {
            return 'Review Required';
        }

        return 'Not Defensible';
    }

    /**
     * @param array<int, string> $selectedArtifacts
     * @param array<string, mixed> $context
     * @return array<int, array<string, mixed>>
     */
    private function artifactPlan(array $selectedArtifacts, array $context): array
    {
        $artifactsById = $this->repository->artifactsById();
        $priority = [];

        foreach ($artifactsById as $artifactId => $artifact) {
            $score = match ((string) $artifact['sensitivity']) {
                'High' => 80,
                'Medium' => 58,
                default => 35,
            };

            $source = strtolower((string) $artifact['source']);
            $category = strtolower((string) $artifact['category']);
            if (in_array($artifactId, $selectedArtifacts, true)) {
                $score -= 28;
            }
            if ($context['paired_smartphone'] && str_contains($source, 'companion')) {
                $score += 8;
            }
            if ($context['suspected_imported_app'] && (str_contains($artifactId, 'script') || str_contains($category, 'application'))) {
                $score += 16;
            }
            if ($context['remote_interactions'] && in_array($artifactId, ['zenbo-master-videophone-db', 'robot-user-profile-db', 'kernel-and-diagnostic-logs'], true)) {
                $score += 10;
            }
            if ($context['privacy_sensitive'] && in_array($artifactId, ['dcim-download-media', 'photos-databases', 'robot-user-profile-db'], true)) {
                $score += 8;
            }

            $priority[] = [
                'id' => $artifactId,
                'name' => (string) $artifact['name'],
                'source' => (string) $artifact['source'],
                'category' => (string) $artifact['category'],
                'priority_score' => max(5, min(100, $score)),
                'status' => in_array($artifactId, $selectedArtifacts, true) ? 'Selected' : 'Recommended',
                'path' => (string) $artifact['path'],
                'evidentiary_value' => (string) $artifact['evidentiary_value'],
                'validation' => (string) $artifact['validation'],
            ];
        }

        usort($priority, static fn (array $left, array $right): int => $right['priority_score'] <=> $left['priority_score']);

        return array_slice($priority, 0, 12);
    }

    /**
     * @param array<int, string> $selectedControls
     * @param array<int, array<string, mixed>> $challengeProfile
     * @return array<int, array<string, mixed>>
     */
    private function recommendations(array $selectedControls, array $challengeProfile): array
    {
        $controlsById = $this->repository->controlsById();
        $ranked = [];

        foreach (array_slice($challengeProfile, 0, 5) as $challenge) {
            foreach ($challenge['controls'] as $controlId) {
                if (!in_array($controlId, $selectedControls, true) && isset($controlsById[$controlId])) {
                    $ranked[$controlId] = ($ranked[$controlId] ?? 0) + (int) $challenge['residual_score'];
                }
            }
        }

        foreach ($controlsById as $controlId => $control) {
            if (!in_array($controlId, $selectedControls, true)) {
                $ranked[$controlId] = ($ranked[$controlId] ?? 0) + (int) $control['weight'];
            }
        }

        arsort($ranked);
        $recommendations = [];
        foreach (array_slice(array_keys($ranked), 0, 8) as $controlId) {
            $control = $controlsById[$controlId];
            $recommendations[] = [
                'id' => $controlId,
                'name' => (string) $control['name'],
                'category' => (string) $control['category'],
                'weight' => (int) $control['weight'],
            ];
        }

        return $recommendations;
    }

    /**
     * @param array<string, mixed> $context
     * @param array<int, array<string, mixed>> $artifactPlan
     * @return array<int, string>
     */
    private function reportOutline(array $context, array $artifactPlan): array
    {
        $outline = [
            'Scope, authority, and device identifiers',
            'Acquisition method, tool versions, and hash manifests',
            'Chain-of-custody and evidence storage notes',
            'Artifact inventory and source prioritization',
            'Timeline reconstruction and timestamp normalization',
            'Cross-source correlation between robot and companion app data',
            'Limitations, negative findings, and confidence ratings',
        ];

        if ($context['suspected_imported_app']) {
            $outline[] = 'Imported app behavior review, manifest consistency, and script analysis';
        }

        if ($artifactPlan !== []) {
            $outline[] = 'Priority artifact: ' . $artifactPlan[0]['name'];
        }

        return $outline;
    }

    /**
     * @param array<int, array<string, mixed>> $recommendations
     * @return array<int, string>
     */
    private function nextActions(string $defensibility, array $recommendations): array
    {
        if ($defensibility === 'Court Ready') {
            return [
                'Complete peer review and lock the report evidence appendix.',
                'Retain hash manifests, tool records, and chain-of-custody records with the case file.',
                'Prepare a concise limitations statement for testimony or executive review.',
            ];
        }

        $actions = [
            'Close the highest residual defensibility gaps before report release.',
            'Correlate robot-side artifacts with companion app records and timeline evidence.',
        ];

        foreach (array_slice($recommendations, 0, 3) as $recommendation) {
            $actions[] = 'Implement: ' . $recommendation['name'] . '.';
        }

        return $actions;
    }

    /**
     * @param mixed $manifest
     * @return array<string, string>
     */
    private function normalizeManifest(mixed $manifest): array
    {
        if (is_string($manifest)) {
            $decoded = json_decode($manifest, true);
            $manifest = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($manifest)) {
            return [];
        }

        $normalized = [];
        foreach ($manifest as $key => $value) {
            if (is_array($value)) {
                $path = (string) ($value['path'] ?? $value['file'] ?? '');
                $hash = (string) ($value['sha256'] ?? $value['hash'] ?? '');
            } else {
                $path = (string) $key;
                $hash = (string) $value;
            }

            $path = trim(str_replace('\\', '/', $path));
            $hash = strtolower(trim($hash));

            if ($path !== '' && preg_match('/^[a-f0-9]{64}$/', $hash)) {
                $normalized[$path] = $hash;
            }
        }

        ksort($normalized);

        return $normalized;
    }

    /**
     * @param array<string, mixed> $added
     * @param array<string, mixed> $removed
     * @param array<string, mixed> $modified
     * @return array<int, string>
     */
    private function hashDiffNotes(array $added, array $removed, array $modified): array
    {
        $notes = [];
        if ($added !== []) {
            $notes[] = 'Added files may represent newly created artifacts, imported apps, media, logs, or acquisition-scope expansion.';
        }
        if ($modified !== []) {
            $notes[] = 'Modified files require artifact-level review because hash changes can reflect user activity, system updates, or evidence-relevant changes.';
        }
        if ($removed !== []) {
            $notes[] = 'Removed files should be reviewed carefully because deletion, cleanup, or acquisition differences may affect interpretation.';
        }
        if ($notes === []) {
            $notes[] = 'No hash differences were detected in the submitted manifests.';
        }

        return $notes;
    }
}

