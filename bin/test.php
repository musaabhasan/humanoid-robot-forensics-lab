<?php

declare(strict_types=1);

use RobotForensicsLab\Repository\LabRepository;
use RobotForensicsLab\Service\ForensicsService;

$bootstrap = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'bootstrap.php';

$repository = new LabRepository($bootstrap['catalog']);
$service = new ForensicsService($repository);
$assertions = 0;

function assertThat(bool $condition, string $message): void
{
    global $assertions;
    $assertions++;

    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$paper = $repository->paper();
assertThat($paper['doi'] === '10.1145/3664476.3670906', 'Paper DOI must match the ACM DOI.');
assertThat(str_contains($paper['citation'], 'ARES 2024'), 'Citation must include ARES 2024.');

$stages = $repository->workflowStages();
$artifacts = $repository->artifactSources();
$challenges = $repository->forensicChallenges();
$controls = $repository->forensicControls();
assertThat(count($stages) === 8, 'Workflow should contain eight stages.');
assertThat(count($artifacts) === 25, 'Artifact catalog should contain 25 sources.');
assertThat(count($challenges) === 8, 'Challenge catalog should contain eight challenges.');
assertThat(count($controls) === 28, 'Control catalog should contain 28 controls.');

$maximum = array_sum(array_map(static fn (array $control): int => (int) $control['weight'], $controls));
assertThat($maximum === 219, 'Maximum control weight should be 219.');

$allControlIds = array_map(static fn (array $control): string => (string) $control['id'], $controls);
$allArtifactIds = array_map(static fn (array $artifact): string => (string) $artifact['id'], $artifacts);
$strong = $service->assessCase([
    'case_name' => 'Strong Zenbo forensic case',
    'robot_model' => 'Zenbo Robot',
    'acquisition_mode' => 'logical-plus-companion',
    'paired_smartphone' => true,
    'remote_interactions' => true,
    'suspected_imported_app' => true,
    'deleted_data_suspected' => false,
    'privacy_sensitive' => false,
    'network_context_available' => true,
    'court_facing_report' => false,
    'controls' => $allControlIds,
    'artifacts' => $allArtifactIds,
]);

assertThat($strong['score'] === 100, 'All controls and artifacts should score 100.');
assertThat($strong['defensibility'] === 'Court Ready', 'Strong case should be court ready.');
assertThat($strong['risk_tier'] === 'Low', 'Strong case should have low residual risk.');

$weak = $service->assessCase([
    'case_name' => 'Weak Zenbo forensic case',
    'robot_model' => 'Zenbo Robot',
    'acquisition_mode' => 'logical',
    'paired_smartphone' => false,
    'remote_interactions' => true,
    'suspected_imported_app' => true,
    'deleted_data_suspected' => true,
    'privacy_sensitive' => true,
    'network_context_available' => false,
    'court_facing_report' => true,
    'controls' => [],
    'artifacts' => [],
]);

assertThat($weak['score'] === 0, 'No controls should produce zero score under high-risk context.');
assertThat($weak['defensibility'] === 'Not Defensible', 'Weak case should not be defensible.');
assertThat($weak['risk_tier'] === 'Critical', 'Weak high-risk case should be critical.');
assertThat($weak['artifact_plan'][0]['priority_score'] >= 90, 'Weak high-risk case should produce high-priority artifacts.');

$partial = $service->assessCase([
    'case_name' => 'Imported app review',
    'suspected_imported_app' => true,
    'paired_smartphone' => true,
    'network_context_available' => true,
    'privacy_sensitive' => false,
    'court_facing_report' => false,
    'controls' => ['case-authorization', 'chain-of-custody', 'baseline-hash-set', 'final-hash-set', 'hash-differencing'],
    'artifacts' => ['blockly-engine-db', 'zenbo-script-folder', 'execute-script-html'],
]);
assertThat($partial['score'] > 20, 'Partial controls should improve score.');
assertThat(count($partial['recommendations']) > 0, 'Partial case should provide recommendations.');
assertThat(in_array('Imported app behavior review, manifest consistency, and script analysis', $partial['report_outline'], true), 'Imported app context should add report section.');

$diff = $service->hashDiff([
    'baseline' => [
        ['path' => '/system/build.prop', 'sha256' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'],
        ['path' => '/sdcard/Logs/UploadRecord.txt', 'sha256' => 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb'],
        ['path' => '/old/file.txt', 'sha256' => 'cccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccc'],
    ],
    'final' => [
        ['path' => '/system/build.prop', 'sha256' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'],
        ['path' => '/sdcard/Logs/UploadRecord.txt', 'sha256' => 'dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd'],
        ['path' => '/DCIM/Camera/zenbo_001.jpg', 'sha256' => 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee'],
    ],
]);
assertThat($diff['summary']['added'] === 1, 'Hash diff should identify one added file.');
assertThat($diff['summary']['removed'] === 1, 'Hash diff should identify one removed file.');
assertThat($diff['summary']['modified'] === 1, 'Hash diff should identify one modified file.');
assertThat($diff['summary']['unchanged'] === 1, 'Hash diff should identify one unchanged file.');

$summary = $service->summary();
assertThat($summary['metrics']['artifact_sources'] === 25, 'Summary should report artifact source count.');
assertThat($summary['metrics']['forensic_controls'] === 28, 'Summary should report control count.');
assertThat($summary['metrics']['maximum_score'] === 219, 'Summary should report maximum score.');

$migration = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR . '001_create_core_tables.sql');
$seed = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR . '001_seed_research_data.sql');
assertThat(is_string($migration) && str_contains($migration, 'CREATE TABLE IF NOT EXISTS case_assessments'), 'Migration must create case assessments table.');
assertThat(is_string($migration) && str_contains($migration, 'CREATE TABLE IF NOT EXISTS hash_diff_runs'), 'Migration must create hash diff table.');
assertThat(is_string($seed) && str_contains($seed, 'zenbo-master-videophone-db'), 'Seed must include Zenbo Master database artifact.');
assertThat(is_string($seed) && str_contains($seed, 'hash-differencing'), 'Seed must include hash differencing control.');

echo 'Tests passed: ' . $assertions . ' assertions.' . PHP_EOL;
