<?php
$pageTitle = 'Job Application';
$pageDescription = 'Apply to join RJ Tide Construction Company, Inc.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Same job list careers.php uses (includes/config.php), filtered to only
// positions currently marked 'open' there, so an applicant can't select a
// posting that isn't actually being hired for. 'Other' is appended since
// it's not a real posting, just a catch-all for anyone else applying.
$openJobs = array_filter($GLOBALS['JOBS'], fn($job) => $job['open']);
$jobs = array_merge(array_column($openJobs, 'title'), ['Other']);

$proficiencyLevels = ['Fair', 'Good', 'Excellent'];
$experienceLevels  = ['None', 'Average', 'Above'];
$experienceSkills  = [
    'concrete_flatwork'   => 'Concrete Flatwork',
    'concrete_foundations' => 'Concrete Foundations',
    'heavy_equipment'     => 'Heavy Equipment Operator',
    'carpentry'           => 'Finish/Rough Carpentry',
    'millwright'          => 'Millwright',
];
$licenses = [
    'State Driver\'s License', 'CDL', 'First Aid/CPR', 'Forklift',
    'ACI Certified', 'Welding', 'NCCCO, Crane Operator', 'NCCCO, Rigger',
];
$physicalRequirements = [
    'Lift 50 lbs, 10 times a day',
    'Climb up to 25 ft vertically without rest',
    'Bend at the waist for long periods of time',
    'Lift 20 lbs overhead, 50 times',
    'Ability to work at 100 ft or higher',
];

$submitted   = isset($_GET['sent']);
$submitError = $_GET['error'] ?? null;
$errorMessages = [
    'validation' => 'Please fill in all required fields with a valid email address.',
    'file'       => 'Your resume must be a PDF or Word document under 5MB.',
    'upload'     => 'We couldn\'t save your resume upload. Please try again.',
];
?>

<section class="page-hero">
    <div class="container">
        <h1>Job Application</h1>
        <a href="<?= BASE_URL ?>/careers.php" class="btn btn--outline" style="margin-top:1rem;">&larr; Go back to Careers</a>
    </div>
</section>

<section>
    <div class="container">
        <?php if ($submitted): ?>
            <div class="form-note form-note--success">Thanks for applying, we've received your application and will be in touch.</div>
        <?php elseif ($submitError): ?>
            <div class="form-note form-note--error"><?= htmlspecialchars($errorMessages[$submitError] ?? 'Something went wrong. Please try again.') ?></div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/apply-handler.php" method="post" enctype="multipart/form-data" style="max-width:720px;">

            <h2 class="form-section-title">Personal Information</h2>
            <div class="form-field">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-field">
                    <label for="phone">Phone *</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
            </div>
            <div class="form-field">
                <label for="address">Present Address</label>
                <input type="text" id="address" name="address">
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city">
                </div>
                <div class="form-field">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state">
                </div>
                <div class="form-field">
                    <label for="zip">Zip</label>
                    <input type="text" id="zip" name="zip">
                </div>
            </div>
            <div class="form-field">
                <label for="emergency_contact">Emergency Contact Name, Relationship &amp; Phone</label>
                <input type="text" id="emergency_contact" name="emergency_contact">
            </div>
            <div class="form-field">
                <label>Are you 18 years of age or older? *</label>
                <div class="checkbox-group">
                    <label class="checkbox-option"><input type="radio" name="age_18" value="Yes" required> Yes</label>
                    <label class="checkbox-option"><input type="radio" name="age_18" value="No"> No</label>
                </div>
            </div>

            <h2 class="form-section-title">Employment Desired</h2>
            <div class="form-row">
                <div class="form-field">
                    <label for="position">Position Applying For *</label>
                    <select id="position" name="position" required>
                        <?php foreach ($jobs as $job): ?>
                        <option value="<?= htmlspecialchars($job) ?>"><?= htmlspecialchars($job) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field">
                    <label for="start_date">Date You Can Start</label>
                    <input type="date" id="start_date" name="start_date">
                </div>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label for="salary_desired">Salary Desired</label>
                    <input type="text" id="salary_desired" name="salary_desired">
                </div>
                <div class="form-field">
                    <label>Are you employed now?</label>
                    <div class="checkbox-group">
                        <label class="checkbox-option"><input type="radio" name="employed_now" value="Yes"> Yes</label>
                        <label class="checkbox-option"><input type="radio" name="employed_now" value="No"> No</label>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label>Have you ever applied/worked for RJ Tide Construction Company before?</label>
                    <div class="checkbox-group">
                        <label class="checkbox-option"><input type="radio" name="applied_before" value="Yes"> Yes</label>
                        <label class="checkbox-option"><input type="radio" name="applied_before" value="No"> No</label>
                    </div>
                </div>
                <div class="form-field">
                    <label for="applied_before_when">If yes, when?</label>
                    <input type="text" id="applied_before_when" name="applied_before_when">
                </div>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label>Can you work weekends?</label>
                    <div class="checkbox-group">
                        <label class="checkbox-option"><input type="radio" name="weekends" value="Yes"> Yes</label>
                        <label class="checkbox-option"><input type="radio" name="weekends" value="No"> No</label>
                    </div>
                </div>
                <div class="form-field">
                    <label>Available to work overtime?</label>
                    <div class="checkbox-group">
                        <label class="checkbox-option"><input type="radio" name="overtime" value="Yes"> Yes</label>
                        <label class="checkbox-option"><input type="radio" name="overtime" value="No"> No</label>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label>Willing to travel?</label>
                    <div class="checkbox-group">
                        <label class="checkbox-option"><input type="radio" name="travel" value="Yes"> Yes</label>
                        <label class="checkbox-option"><input type="radio" name="travel" value="No"> No</label>
                    </div>
                </div>
                <div class="form-field">
                    <label>Willing to stay overnight?</label>
                    <div class="checkbox-group">
                        <label class="checkbox-option"><input type="radio" name="overnight" value="Yes"> Yes</label>
                        <label class="checkbox-option"><input type="radio" name="overnight" value="No"> No</label>
                    </div>
                </div>
            </div>

            <h2 class="form-section-title">General</h2>
            <div class="form-field">
                <label for="special_training">Special Training</label>
                <input type="text" id="special_training" name="special_training">
            </div>
            <div class="form-field">
                <label for="special_skills">Special Skills</label>
                <input type="text" id="special_skills" name="special_skills">
            </div>
            <div class="form-field">
                <label for="primary_language">Primary Language</label>
                <input type="text" id="primary_language" name="primary_language">
            </div>
            <div class="form-row">
                <?php foreach (['speak' => 'Speak', 'read' => 'Read', 'write' => 'Write'] as $skillKey => $skillLabel): ?>
                <div class="form-field">
                    <label for="primary_language_<?= $skillKey ?>"><?= $skillLabel ?></label>
                    <select id="primary_language_<?= $skillKey ?>" name="primary_language_<?= $skillKey ?>">
                        <option value="">-</option>
                        <?php foreach ($proficiencyLevels as $level): ?>
                        <option value="<?= $level ?>"><?= $level ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="form-field">
                <label for="other_language">Other Language</label>
                <input type="text" id="other_language" name="other_language">
            </div>
            <div class="form-row">
                <?php foreach (['speak' => 'Speak', 'read' => 'Read', 'write' => 'Write'] as $skillKey => $skillLabel): ?>
                <div class="form-field">
                    <label for="other_language_<?= $skillKey ?>"><?= $skillLabel ?></label>
                    <select id="other_language_<?= $skillKey ?>" name="other_language_<?= $skillKey ?>">
                        <option value="">-</option>
                        <?php foreach ($proficiencyLevels as $level): ?>
                        <option value="<?= $level ?>"><?= $level ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endforeach; ?>
            </div>

            <h2 class="form-section-title">Education</h2>
            <?php foreach (['high_school' => 'High School', 'college' => 'College', 'other_education' => 'Other'] as $eduKey => $eduLabel): ?>
            <div class="form-row">
                <div class="form-field">
                    <label for="<?= $eduKey ?>_city_state"><?= $eduLabel ?>, City/State</label>
                    <input type="text" id="<?= $eduKey ?>_city_state" name="<?= $eduKey ?>_city_state">
                </div>
                <div class="form-field">
                    <label for="<?= $eduKey ?>_degree"><?= $eduLabel ?>, Graduate/Degree</label>
                    <input type="text" id="<?= $eduKey ?>_degree" name="<?= $eduKey ?>_degree">
                </div>
            </div>
            <?php endforeach; ?>

            <h2 class="form-section-title">Experience</h2>
            <?php foreach ($experienceSkills as $skillKey => $skillLabel): ?>
            <div class="form-field">
                <label for="experience_<?= $skillKey ?>"><?= htmlspecialchars($skillLabel) ?></label>
                <select id="experience_<?= $skillKey ?>" name="experience_<?= $skillKey ?>">
                    <option value="">-</option>
                    <?php foreach ($experienceLevels as $level): ?>
                    <option value="<?= $level ?>"><?= $level ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endforeach; ?>

            <h2 class="form-section-title">Valid Licenses/Certifications</h2>
            <div class="form-field">
                <div class="checkbox-group">
                    <?php foreach ($licenses as $license): ?>
                    <label class="checkbox-option">
                        <input type="checkbox" name="licenses[]" value="<?= htmlspecialchars($license) ?>">
                        <?= htmlspecialchars($license) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <h2 class="form-section-title">Physical Requirements</h2>
            <p class="form-field-hint">Check any of the following you're able to do.</p>
            <div class="form-field">
                <div class="checkbox-group">
                    <?php foreach ($physicalRequirements as $requirement): ?>
                    <label class="checkbox-option">
                        <input type="checkbox" name="physical[]" value="<?= htmlspecialchars($requirement) ?>">
                        <?= htmlspecialchars($requirement) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <h2 class="form-section-title">Employment History</h2>
            <p class="form-field-hint">Begin with the most recent. Optional if you're attaching a resume below.</p>
            <?php for ($i = 1; $i <= 3; $i++): ?>
            <div style="padding:16px 0;<?= $i < 3 ? 'border-bottom:1px solid #eee;margin-bottom:8px;' : '' ?>">
                <div class="form-field">
                    <label for="employer<?= $i ?>_company">Employer #<?= $i ?>, Name &amp; Address of Company</label>
                    <input type="text" id="employer<?= $i ?>_company" name="employer[<?= $i ?>][company]">
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label for="employer<?= $i ?>_from">Employed From</label>
                        <input type="text" id="employer<?= $i ?>_from" name="employer[<?= $i ?>][from]" placeholder="MM/YYYY">
                    </div>
                    <div class="form-field">
                        <label for="employer<?= $i ?>_to">Employed To</label>
                        <input type="text" id="employer<?= $i ?>_to" name="employer[<?= $i ?>][to]" placeholder="MM/YYYY">
                    </div>
                </div>
                <div class="form-field">
                    <label for="employer<?= $i ?>_title">Title &amp; Duties</label>
                    <input type="text" id="employer<?= $i ?>_title" name="employer[<?= $i ?>][title]">
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label for="employer<?= $i ?>_starting_wage">Starting Wage</label>
                        <input type="text" id="employer<?= $i ?>_starting_wage" name="employer[<?= $i ?>][starting_wage]">
                    </div>
                    <div class="form-field">
                        <label for="employer<?= $i ?>_final_wage">Final Wage</label>
                        <input type="text" id="employer<?= $i ?>_final_wage" name="employer[<?= $i ?>][final_wage]">
                    </div>
                </div>
                <div class="form-field">
                    <label for="employer<?= $i ?>_reason">Reason for Leaving</label>
                    <input type="text" id="employer<?= $i ?>_reason" name="employer[<?= $i ?>][reason]">
                </div>
                <div class="form-field">
                    <label for="employer<?= $i ?>_supervisor">Supervisor Name and Contact Phone Number</label>
                    <input type="text" id="employer<?= $i ?>_supervisor" name="employer[<?= $i ?>][supervisor]">
                </div>
            </div>
            <?php endfor; ?>

            <h2 class="form-section-title">Resume &amp; Additional Info</h2>
            <div class="form-field">
                <label for="resume">Resume (PDF or Word, max 5MB, optional)</label>
                <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx">
            </div>
            <div class="form-field">
                <label for="message">Anything else you'd like us to know?</label>
                <textarea id="message" name="message"></textarea>
            </div>

            <div class="form-field">
                <label class="checkbox-option" style="font-weight:600;">
                    <input type="checkbox" name="certify" value="1" required>
                    I certify that the information provided in this application is complete and true
                    to the best of my knowledge, and I authorize RJ Tide Construction to verify it with
                    the references, schools, and employers listed above. *
                </label>
            </div>

            <div style="position:absolute;left:-9999px;" aria-hidden="true">
                <label for="website">Leave blank</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>
            <button type="submit" class="btn">Submit Application</button>
        </form>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
