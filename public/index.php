<?php

declare(strict_types=1);

use RobotForensicsLab\Repository\LabRepository;
use RobotForensicsLab\Security\Csrf;
use RobotForensicsLab\Security\SecurityHeaders;
use RobotForensicsLab\Service\ForensicsService;
use RobotForensicsLab\Support\Database;
use RobotForensicsLab\Support\Json;
use RobotForensicsLab\Support\View;

$bootstrap = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'bootstrap.php';
$repository = new LabRepository($bootstrap['catalog'], Database::connection());
$service = new ForensicsService($repository);
SecurityHeaders::apply();

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($path === '/health') {
    Json::respond([
        'status' => 'ok',
        'service' => 'humanoid-robot-forensics-lab',
        'paper_doi' => $repository->paper()['doi'],
    ]);
}

if ($path === '/api/summary') {
    Json::respond($service->summary());
}

if ($path === '/api/assess') {
    if ($method !== 'POST') {
        Json::respond(['error' => 'POST required'], 405);
    }
    $payload = json_decode(file_get_contents('php://input') ?: '{}', true);
    if (!is_array($payload) || $payload === []) {
        $payload = $_POST;
    }
    Json::respond($service->assessCase($payload));
}

if ($path === '/api/hash-diff') {
    if ($method !== 'POST') {
        Json::respond(['error' => 'POST required'], 405);
    }
    $payload = json_decode(file_get_contents('php://input') ?: '{}', true);
    if (!is_array($payload) || $payload === []) {
        $payload = $_POST;
    }
    Json::respond($service->hashDiff($payload));
}

if ($path === '/') {
    echo View::page('Robot Forensics Lab', renderDashboard($repository, $service));
    exit;
}

if ($path === '/casework') {
    echo View::page('Casework | Robot Forensics Lab', renderCasework($repository, $service, $method));
    exit;
}

if ($path === '/artifacts') {
    echo View::page('Artifacts | Robot Forensics Lab', renderArtifacts($repository));
    exit;
}

if ($path === '/hash-diff') {
    echo View::page('Hash Diff | Robot Forensics Lab', renderHashDiff($service, $method));
    exit;
}

if ($path === '/paper') {
    echo View::page('Paper | Robot Forensics Lab', renderPaper($repository));
    exit;
}

http_response_code(404);
echo View::page('Not Found | Robot Forensics Lab', '<section class="panel hero"><h1>Page not found</h1><p>The requested page is not available.</p></section>');

function renderDashboard(LabRepository $repository, ForensicsService $service): string
{
    $summary = $service->summary();
    $paper = $repository->paper();
    $metrics = $summary['metrics'];
    $stages = $repository->workflowStages();
    $artifacts = array_slice($repository->artifactSources(), 0, 6);
    $recent = $summary['recent_assessments'];

    $stageHtml = '';
    foreach ($stages as $index => $stage) {
        $stageHtml .= '<article class="stage-card"><span>Stage ' . ($index + 1) . '</span><h3>' . View::e($stage['name']) . '</h3><p>' . View::e($stage['purpose']) . '</p></article>';
    }

    $artifactHtml = '';
    foreach ($artifacts as $artifact) {
        $artifactHtml .= '<article class="card">'
            . '<span>' . View::e($artifact['category']) . '</span>'
            . '<h3>' . View::e($artifact['name']) . '</h3>'
            . '<p>' . View::e($artifact['evidentiary_value']) . '</p>'
            . '</article>';
    }

    $recentHtml = '<p class="muted">Case assessments are stored when a database connection is configured.</p>';
    if ($recent !== []) {
        $recentHtml = '';
        foreach ($recent as $item) {
            $recentHtml .= '<div><strong>' . View::e($item['case_name']) . '</strong><span>' . View::e($item['score']) . '/100 - ' . View::e($item['risk_tier']) . '</span></div>';
        }
    }

    return <<<HTML
<section class="panel hero">
  <div>
    <p class="eyebrow">Humanoid Robot Cybersecurity and Forensics</p>
    <h1>Advanced casework lab for robot, companion app, and IoT evidence triage.</h1>
    <p class="lead">A PHP/MySQL platform based on the ARES 2024 Zenbo robot forensic investigation by Farkhund Iqbal, Abdullah Kazim, Aine MacDermott, Richard Ikuesan, Musaab Hasan, and Andrew Marrington.</p>
    <div class="hero-actions">
      <a class="button-link" href="/casework">Run Case Assessment</a>
      <a class="secondary-link" href="/hash-diff">Compare Hash Manifests</a>
    </div>
  </div>
  <aside class="paper-card">
    <span>Paper Reference</span>
    <strong>{$paper['title']}</strong>
    <p>{$paper['venue_short']} - Article {$paper['article_number']} - {$paper['pages']} pages</p>
    <a href="{$paper['doi_url']}" target="_blank" rel="noreferrer">{$paper['doi']}</a>
  </aside>
</section>

<section class="metric-grid">
  <article><span>Workflow stages</span><strong>{$metrics['workflow_stages']}</strong><p>Authorization, acquisition, differencing, analysis, and reporting.</p></article>
  <article><span>Artifact sources</span><strong>{$metrics['artifact_sources']}</strong><p>Robot, companion app, media, package, log, and system artifacts.</p></article>
  <article><span>Forensic challenges</span><strong>{$metrics['forensic_challenges']}</strong><p>Distributed evidence, logical limits, timestamp friction, and privacy risk.</p></article>
  <article><span>Controls</span><strong>{$metrics['forensic_controls']}</strong><p>Defensibility controls across evidence handling and analysis.</p></article>
</section>

<section class="section-head"><h2>Forensic Workflow</h2><a href="/casework">Assess a case</a></section>
<div class="stage-grid">{$stageHtml}</div>

<section class="section-head"><h2>High-Value Artifact Sources</h2><a href="/artifacts">Full artifact catalog</a></section>
<div class="card-grid">{$artifactHtml}</div>

<section class="panel recent-panel">
  <h2>Recent Case Assessments</h2>
  <div class="recent-list">{$recentHtml}</div>
</section>
HTML;
}

function renderCasework(LabRepository $repository, ForensicsService $service, string $method): string
{
    $result = null;
    $notice = '';
    if ($method === 'POST') {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $notice = '<div class="notice">The case assessment could not be submitted because the session token expired.</div>';
        } else {
            $result = $service->assessCase($_POST);
        }
    }

    $controlsByCategory = [];
    foreach ($repository->forensicControls() as $control) {
        $controlsByCategory[(string) $control['category']][] = $control;
    }

    $controlHtml = '';
    foreach ($controlsByCategory as $category => $controls) {
        $controlHtml .= '<fieldset class="control-set"><legend>' . View::e($category) . '</legend><div class="control-grid">';
        foreach ($controls as $control) {
            $checked = $result && in_array($control['id'], $result['selected_controls'], true) ? ' checked' : '';
            $controlHtml .= '<label class="control-item"><input type="checkbox" name="controls[]" value="' . View::e($control['id']) . '"' . $checked . '><span><strong>' . View::e($control['name']) . '</strong><small>Weight ' . View::e($control['weight']) . '</small></span></label>';
        }
        $controlHtml .= '</div></fieldset>';
    }

    $artifactOptions = '';
    foreach (array_slice($repository->artifactSources(), 0, 14) as $artifact) {
        $checked = $result && in_array($artifact['id'], $result['selected_artifacts'], true) ? ' checked' : '';
        $artifactOptions .= '<label class="control-item"><input type="checkbox" name="artifacts[]" value="' . View::e($artifact['id']) . '"' . $checked . '><span><strong>' . View::e($artifact['name']) . '</strong><small>' . View::e($artifact['source']) . '</small></span></label>';
    }

    $resultHtml = '';
    if ($result !== null) {
        $recommendationHtml = '';
        foreach ($result['recommendations'] as $recommendation) {
            $recommendationHtml .= '<li><strong>' . View::e($recommendation['name']) . '</strong><span>' . View::e($recommendation['category']) . '</span></li>';
        }

        $artifactPlanHtml = '';
        foreach (array_slice($result['artifact_plan'], 0, 6) as $artifact) {
            $artifactPlanHtml .= '<article class="card">'
                . '<span>' . View::e($artifact['status']) . ' - priority ' . View::e($artifact['priority_score']) . '</span>'
                . '<h3>' . View::e($artifact['name']) . '</h3>'
                . '<p>' . View::e($artifact['validation']) . '</p>'
                . '</article>';
        }

        $challengeHtml = '';
        foreach (array_slice($result['challenge_profile'], 0, 6) as $challenge) {
            $challengeHtml .= '<tr>'
                . '<td>' . View::e($challenge['name']) . '</td>'
                . '<td>' . View::e($challenge['severity']) . '</td>'
                . '<td>' . View::e($challenge['residual_score']) . '</td>'
                . '<td>' . View::e($challenge['residual_tier']) . '</td>'
                . '</tr>';
        }

        $resultHtml = <<<HTML
<section class="panel result-panel">
  <div class="result-score">
    <span>Defensibility score</span>
    <strong>{$result['score']}</strong>
    <p>{$result['defensibility']} - {$result['risk_tier']} residual risk</p>
  </div>
  <div>
    <h2>Priority Controls</h2>
    <ol class="recommendation-list">{$recommendationHtml}</ol>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Challenge</th><th>Severity</th><th>Residual</th><th>Tier</th></tr></thead>
      <tbody>{$challengeHtml}</tbody>
    </table>
  </div>
</section>
<section class="section-head"><h2>Artifact Triage Plan</h2></section>
<div class="card-grid">{$artifactPlanHtml}</div>
HTML;
    }

    $csrf = Csrf::token();

    return <<<HTML
<section class="panel form-panel">
  <p class="eyebrow">Casework Assessment</p>
  <h1>Score forensic defensibility for a humanoid robot investigation.</h1>
  {$notice}
  <form method="post" action="/casework">
    <input type="hidden" name="_csrf" value="{$csrf}">
    <div class="form-grid">
      <label>Case name<input name="case_name" placeholder="Zenbo witness case review"></label>
      <label>Robot model<input name="robot_model" placeholder="Zenbo Robot"></label>
      <label>Acquisition mode
        <select name="acquisition_mode">
          <option value="logical">Logical acquisition</option>
          <option value="logical-plus-companion">Logical plus companion app</option>
          <option value="physical-review">Physical acquisition review</option>
          <option value="unknown">Unknown</option>
        </select>
      </label>
    </div>
    <div class="toggle-row">
      <label><input type="checkbox" name="paired_smartphone" value="1" checked> Paired smartphone available</label>
      <label><input type="checkbox" name="remote_interactions" value="1" checked> Remote interactions relevant</label>
      <label><input type="checkbox" name="suspected_imported_app" value="1"> Imported app behavior is in scope</label>
      <label><input type="checkbox" name="deleted_data_suspected" value="1"> Deleted data is suspected</label>
      <label><input type="checkbox" name="privacy_sensitive" value="1" checked> Sensitive household or personal data present</label>
      <label><input type="checkbox" name="network_context_available" value="1"> Network context available</label>
      <label><input type="checkbox" name="court_facing_report" value="1" checked> Court-facing or formal report</label>
    </div>
    <fieldset class="control-set">
      <legend>Artifact sources reviewed</legend>
      <div class="control-grid">{$artifactOptions}</div>
    </fieldset>
    {$controlHtml}
    <button type="submit">Calculate Defensibility</button>
  </form>
</section>
{$resultHtml}
HTML;
}

function renderArtifacts(LabRepository $repository): string
{
    $html = '<section class="panel paper-detail"><p class="eyebrow">Artifact Catalog</p><h1>Robot and companion-app evidence sources.</h1><p class="lead">Artifact sources are mapped to evidentiary value, validation strategy, source system, path, and sensitivity.</p></section>';
    $groups = [];
    foreach ($repository->artifactSources() as $artifact) {
        $groups[(string) $artifact['category']][] = $artifact;
    }

    foreach ($groups as $category => $artifacts) {
        $html .= '<section class="section-head"><h2>' . View::e($category) . '</h2><span>' . count($artifacts) . ' sources</span></section><div class="card-grid artifact-grid">';
        foreach ($artifacts as $artifact) {
            $html .= '<article class="card artifact-card">'
                . '<span>' . View::e($artifact['sensitivity']) . ' sensitivity</span>'
                . '<h3>' . View::e($artifact['name']) . '</h3>'
                . '<p><strong>Source:</strong> ' . View::e($artifact['source']) . '</p>'
                . '<p><strong>Path:</strong> ' . View::e($artifact['path']) . '</p>'
                . '<p>' . View::e($artifact['evidentiary_value']) . '</p>'
                . '<small>Validation: ' . View::e($artifact['validation']) . '</small>'
                . '</article>';
        }
        $html .= '</div>';
    }

    return $html;
}

function renderHashDiff(ForensicsService $service, string $method): string
{
    $result = null;
    $notice = '';
    if ($method === 'POST') {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $notice = '<div class="notice">The hash comparison could not be submitted because the session token expired.</div>';
        } else {
            $result = $service->hashDiff([
                'baseline' => $_POST['baseline'] ?? '',
                'final' => $_POST['final'] ?? '',
            ]);
        }
    }

    $resultHtml = '';
    if ($result !== null) {
        $summary = $result['summary'];
        $notes = '';
        foreach ($result['integrity_notes'] as $note) {
            $notes .= '<li>' . View::e($note) . '</li>';
        }

        $resultHtml = <<<HTML
<section class="panel result-panel compact-result">
  <div class="result-score">
    <span>Changed files</span>
    <strong>{$summary['modified']}</strong>
    <p>{$summary['added']} added - {$summary['removed']} removed - {$summary['unchanged']} unchanged</p>
  </div>
  <div>
    <h2>Integrity Notes</h2>
    <ul class="recommendation-list">{$notes}</ul>
  </div>
</section>
HTML;
    }

    $sampleBaseline = '[{"path":"/system/build.prop","sha256":"aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa"},{"path":"/sdcard/Logs/UploadRecord.txt","sha256":"bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb"}]';
    $sampleFinal = '[{"path":"/system/build.prop","sha256":"aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa"},{"path":"/sdcard/Logs/UploadRecord.txt","sha256":"cccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccc"},{"path":"/DCIM/Camera/zenbo_001.jpg","sha256":"dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd"}]';
    $csrf = Csrf::token();

    return <<<HTML
<section class="panel form-panel">
  <p class="eyebrow">SHA-256 Manifest Differencing</p>
  <h1>Compare baseline and final acquisition manifests.</h1>
  <p class="lead">Paste JSON as either an object of path-to-hash pairs or an array of objects containing path and sha256/hash values.</p>
  {$notice}
  <form method="post" action="/hash-diff">
    <input type="hidden" name="_csrf" value="{$csrf}">
    <label>Baseline manifest<textarea name="baseline" rows="8">{$sampleBaseline}</textarea></label>
    <label>Final manifest<textarea name="final" rows="8">{$sampleFinal}</textarea></label>
    <button type="submit">Compare Manifests</button>
  </form>
</section>
{$resultHtml}
HTML;
}

function renderPaper(LabRepository $repository): string
{
    $paper = $repository->paper();
    $keywordHtml = '';
    foreach ($paper['keywords'] as $keyword) {
        $keywordHtml .= '<span class="pill">' . View::e($keyword) . '</span>';
    }

    $challengeHtml = '';
    foreach ($repository->forensicChallenges() as $challenge) {
        $challengeHtml .= '<article class="card">'
            . '<span>' . View::e($challenge['severity']) . ' priority</span>'
            . '<h3>' . View::e($challenge['name']) . '</h3>'
            . '<p>' . View::e($challenge['description']) . '</p>'
            . '</article>';
    }

    return <<<HTML
<section class="panel paper-detail">
  <p class="eyebrow">Research Alignment</p>
  <h1>{$paper['title']}</h1>
  <p class="lead">{$paper['summary']}</p>
  <div class="paper-citation">
    <span>Formal citation</span>
    <p>{$paper['citation']}</p>
  </div>
  <div class="hero-actions">
    <a class="button-link" href="{$paper['doi_url']}" target="_blank" rel="noreferrer">DOI</a>
    <a class="secondary-link" href="{$paper['acm_url']}" target="_blank" rel="noreferrer">ACM</a>
    <a class="secondary-link" href="{$paper['zu_record_url']}" target="_blank" rel="noreferrer">ZU Record</a>
  </div>
</section>
<section class="section-head"><h2>Keywords</h2></section>
<div class="keyword-row">{$keywordHtml}</div>
<section class="section-head"><h2>Forensic Challenges</h2></section>
<div class="card-grid">{$challengeHtml}</div>
HTML;
}

