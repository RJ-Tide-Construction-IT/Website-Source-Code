<?php
// Everything about job listings, loaded by includes/config.php:
//   - the full job list: the built-in postings ($GLOBALS['JOBS'] in config.php,
//     each a hand-written page in careers/) plus postings added on the Employee
//     Dashboard's Job Postings page (shown by careers/posting.php)
//   - which jobs are open (the dashboard's Job Openings page)
// Both are saved as small files in uploads/dashboard/, not the database, so the
// public Careers page keeps working even if the dashboard's database doesn't.

// Saves $data as a JSON file. Writes a temporary file, then swaps it in, so the
// public site never reads a half-written file.
function write_json_file(string $file, array $data): void {
    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0755, true);
    }
    $temp = $file . '.tmp';
    if (file_put_contents($temp, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX) === false || !rename($temp, $file)) {
        throw new RuntimeException('Could not save ' . $file);
    }
}

function read_json_file(string $file): ?array {
    if (!is_file($file)) {
        return null;
    }
    $data = json_decode((string) file_get_contents($file), true);
    return is_array($data) ? $data : null;
}

// ---------- Postings added on the dashboard ----------

// Keyed by id (the posting's web address, careers/posting.php?id=...). Each:
//   title, salary, classification, reports_to   (all but title may be '')
//   body               description in the simple format, see render_posting_body()
//   standard_sections  true to add the shared EEO / Work Authorization / Other Duties text
//   updated_by, updated_at (UTC)
function custom_job_postings(): array {
    $postings = read_json_file(JOB_POSTINGS_FILE)['postings'] ?? [];
    return is_array($postings) ? $postings : [];
}

function save_custom_job_postings(array $postings): void {
    write_json_file(JOB_POSTINGS_FILE, ['postings' => $postings]);
}

// A web-address-friendly id from a title, e.g. "Shop Welder II" => "shop-welder-ii",
// with -2, -3... added if it's already taken.
function new_posting_id(string $title): string {
    $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)), '-') ?: 'job';
    $taken = array_keys(custom_job_postings());
    $id = $base;
    for ($n = 2; in_array($id, $taken, true); $n++) {
        $id = "$base-$n";
    }
    return $id;
}

// Every job, built-in and dashboard-added, as ['title', 'href', 'open'] like
// $GLOBALS['JOBS']. Dashboard-added ones also have 'posting_id', and start out
// not open until someone marks them as hiring.
function all_jobs(): array {
    $jobs = $GLOBALS['JOBS'];
    foreach (custom_job_postings() as $id => $posting) {
        $jobs[] = [
            'title'      => $posting['title'],
            'href'       => '/careers/posting.php?id=' . rawurlencode($id),
            'open'       => false,
            'posting_id' => $id,
        ];
    }
    return $jobs;
}

// ---------- Which jobs are open ----------

// As last saved on the dashboard, or null if nothing has been saved yet. Holds:
//   open       titles currently being hired for
//   known      every title that existed when it was saved (see jobs_with_status())
//   updated_by / updated_at   who saved it last, and when (UTC)
function saved_job_openings(): ?array {
    return read_json_file(JOB_OPENINGS_FILE);
}

function save_job_openings(array $openTitles, string $updatedBy): void {
    write_json_file(JOB_OPENINGS_FILE, [
        'open'       => array_values($openTitles),
        'known'      => array_column(all_jobs(), 'title'),
        'updated_by' => $updatedBy,
        'updated_at' => gmdate('Y-m-d H:i:s'),
    ]);
}

// all_jobs() with each job's 'open' value as last saved on the dashboard.
// Uses each job's default for any job the dashboard hasn't saved a choice for
// yet (nothing saved so far, or a job added to config.php since), and if the
// saved file can't be read, so the public Careers page never breaks over it.
function jobs_with_status(): array {
    $saved = saved_job_openings();
    $open  = is_array($saved['open'] ?? null) ? $saved['open'] : [];
    $known = is_array($saved['known'] ?? null) ? $saved['known'] : [];
    return array_map(function ($job) use ($open, $known) {
        if (in_array($job['title'], $known, true)) {
            $job['open'] = in_array($job['title'], $open, true);
        }
        return $job;
    }, all_jobs());
}

function open_job_titles(): array {
    return array_column(array_filter(jobs_with_status(), fn($job) => $job['open']), 'title');
}

// The salary / classification / reports-to row for a dashboard-added posting,
// in the $jobMeta format includes/job-posting-header.php expects. Blank ones are left out.
function posting_job_meta(array $posting): array {
    return array_filter([
        'Salary Range'   => $posting['salary'] ?? '',
        'Classification' => $posting['classification'] ?? '',
        'Reports to'     => $posting['reports_to'] ?? '',
    ], fn($value) => $value !== '');
}

// ---------- Posting description format ----------

// Turns a posting description into HTML, using a deliberately simple format
// anyone can type in a plain text box:
//   # Heading          a line starting with "# " is a section heading
//   - Bullet point     lines starting with "- " (or "* ") make a bulleted list
//   Plain text         everything else is a paragraph; a blank line starts a new one
// All text is escaped, so nothing typed here can add its own HTML or scripts.
function render_posting_body(string $body): string {
    $html = '';
    $paragraph = [];
    $bullets = [];
    $flush = function () use (&$html, &$paragraph, &$bullets) {
        if ($paragraph) {
            $html .= '<p>' . htmlspecialchars(implode(' ', $paragraph)) . "</p>\n";
            $paragraph = [];
        }
        if ($bullets) {
            $html .= "<ul>\n" . implode('', array_map(fn($b) => '    <li>' . htmlspecialchars($b) . "</li>\n", $bullets)) . "</ul>\n";
            $bullets = [];
        }
    };

    foreach (preg_split('/\R/u', $body) as $line) {
        $line = trim($line);
        if ($line === '') {
            $flush();
        } elseif (preg_match('/^#\s+(.+)$/u', $line, $m)) {
            $flush();
            $html .= '<h3>' . htmlspecialchars($m[1]) . "</h3>\n";
        } elseif (preg_match('/^[-*]\s+(.+)$/u', $line, $m)) {
            if ($paragraph) {
                $flush();
            }
            $bullets[] = $m[1];
        } else {
            if ($bullets) {
                $flush();
            }
            $paragraph[] = $line;
        }
    }
    $flush();
    return $html;
}
