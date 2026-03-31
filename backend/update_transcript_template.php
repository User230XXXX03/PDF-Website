<?php
/**
 * Update student transcript template
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$newTemplate = '<html>
<head>
<style>
body {
    font-family: "SimHei", "Microsoft YaHei", Arial, sans-serif;
    padding: 30px;
    line-height: 1.5;
    color: #333;
}
.watermark {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 80px;
    color: #f0f0f0;
    opacity: 0.3;
    z-index: -1;
}
.header {
    text-align: center;
    border-bottom: 4px double #2c3e50;
    padding-bottom: 15px;
    margin-bottom: 20px;
}
.header h1 {
    color: #2c3e50;
    margin: 5px 0;
    font-size: 28px;
    font-weight: bold;
}
.header .subtitle {
    color: #7f8c8d;
    font-size: 14px;
    margin: 5px 0;
}
.doc-number {
    text-align: right;
    font-size: 12px;
    color: #7f8c8d;
    margin: 10px 0;
}
.student-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    margin: 20px 0;
    border-radius: 8px;
}
.info-grid {
    display: table;
    width: 100%;
}
.info-row {
    display: table-row;
}
.info-cell {
    display: table-cell;
    padding: 8px;
    width: 50%;
}
.info-label {
    font-weight: bold;
    opacity: 0.9;
}
.info-value {
    margin-left: 10px;
}
.section-title {
    color: #2c3e50;
    font-size: 18px;
    font-weight: bold;
    margin: 25px 0 15px 0;
    padding-bottom: 8px;
    border-bottom: 2px solid #3498db;
}
.grades-table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.grades-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.grades-table th {
    padding: 12px 8px;
    text-align: center;
    font-weight: bold;
    font-size: 14px;
}
.grades-table td {
    border: 1px solid #e0e0e0;
    padding: 10px 8px;
    text-align: center;
}
.grades-table tbody tr:nth-child(odd) {
    background: #f9f9f9;
}
.grades-table tbody tr:hover {
    background: #e3f2fd;
}
.grade-excellent {
    color: #27ae60;
    font-weight: bold;
}
.grade-good {
    color: #3498db;
    font-weight: bold;
}
.grade-pass {
    color: #f39c12;
}
.summary-box {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 20px;
    margin: 20px 0;
    border-radius: 8px;
}
.summary-grid {
    display: table;
    width: 100%;
}
.summary-item {
    display: table-cell;
    padding: 10px;
    text-align: center;
    width: 33.33%;
}
.summary-label {
    font-size: 12px;
    opacity: 0.9;
    margin-bottom: 5px;
}
.summary-value {
    font-size: 24px;
    font-weight: bold;
}
.evaluation {
    background: #fff9e6;
    border-left: 4px solid #f39c12;
    padding: 15px 20px;
    margin: 20px 0;
}
.evaluation-title {
    font-weight: bold;
    color: #e67e22;
    margin-bottom: 10px;
}
.performance-bar {
    background: #ecf0f1;
    height: 25px;
    border-radius: 12px;
    overflow: hidden;
    margin: 10px 0;
}
.performance-fill {
    background: linear-gradient(90deg, #56ab2f 0%, #a8e063 100%);
    height: 100%;
    text-align: center;
    line-height: 25px;
    color: white;
    font-weight: bold;
    font-size: 12px;
}
.signature-area {
    margin-top: 40px;
    display: table;
    width: 100%;
}
.signature-box {
    display: table-cell;
    width: 50%;
    padding: 20px;
}
.signature-line {
    border-top: 1px solid #333;
    margin-top: 40px;
    padding-top: 5px;
    text-align: center;
    font-size: 12px;
}
.footer {
    text-align: center;
    margin-top: 30px;
    padding-top: 15px;
    border-top: 1px solid #ddd;
    font-size: 11px;
    color: #7f8c8d;
}
.seal {
    text-align: right;
    margin-top: -80px;
    color: #c0392b;
    font-size: 60px;
    opacity: 0.6;
    font-weight: bold;
}
</style>
</head>
<body>

<div class="watermark">Transcript</div>

<div class="doc-number">
Document No.: TR-{{STUDENT_ID}}-{{SEMESTER}}
</div>

<div class="header">
    <h1>Student Transcript</h1>
    <p class="subtitle">Academic Year 2024-2025</p>
    <p class="subtitle">{{UNIVERSITY_NAME}}</p>
</div>

<div class="student-info">
    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell">
                <span class="info-label">Name:</span>
                <span class="info-value">{{STUDENT_NAME}}</span>
            </div>
            <div class="info-cell">
                <span class="info-label">Student ID:</span>
                <span class="info-value">{{STUDENT_ID}}</span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-cell">
                <span class="info-label">Class:</span>
                <span class="info-value">{{CLASS_NAME}}</span>
            </div>
            <div class="info-cell">
                <span class="info-label">Major:</span>
                <span class="info-value">{{MAJOR}}</span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-cell">
                <span class="info-label">Semester:</span>
                <span class="info-value">{{SEMESTER}}</span>
            </div>
            <div class="info-cell">
                <span class="info-label">Enrollment Year:</span>
                <span class="info-value">{{ENROLLMENT_YEAR}}</span>
            </div>
        </div>
    </div>
</div>

<div class="section-title">Course Grades</div>

<table class="grades-table">
    <thead>
        <tr>
            <th style="width: 8%;">No.</th>
            <th style="width: 25%;">Course Name</th>
            <th style="width: 15%;">Code</th>
            <th style="width: 10%;">Credits</th>
            <th style="width: 12%;">Score</th>
            <th style="width: 10%;">GPA</th>
            <th style="width: 10%;">Grade</th>
            <th style="width: 10%;">Hours</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td style="text-align: left;">{{COURSE_1}}</td>
            <td>{{CODE_1}}</td>
            <td>{{CREDIT_1}}</td>
            <td class="grade-excellent">{{SCORE_1}}</td>
            <td>{{GPA_1}}</td>
            <td>{{LEVEL_1}}</td>
            <td>{{HOURS_1}}</td>
        </tr>
        <tr>
            <td>2</td>
            <td style="text-align: left;">{{COURSE_2}}</td>
            <td>{{CODE_2}}</td>
            <td>{{CREDIT_2}}</td>
            <td class="grade-good">{{SCORE_2}}</td>
            <td>{{GPA_2}}</td>
            <td>{{LEVEL_2}}</td>
            <td>{{HOURS_2}}</td>
        </tr>
        <tr>
            <td>3</td>
            <td style="text-align: left;">{{COURSE_3}}</td>
            <td>{{CODE_3}}</td>
            <td>{{CREDIT_3}}</td>
            <td class="grade-excellent">{{SCORE_3}}</td>
            <td>{{GPA_3}}</td>
            <td>{{LEVEL_3}}</td>
            <td>{{HOURS_3}}</td>
        </tr>
        <tr>
            <td>4</td>
            <td style="text-align: left;">{{COURSE_4}}</td>
            <td>{{CODE_4}}</td>
            <td>{{CREDIT_4}}</td>
            <td class="grade-good">{{SCORE_4}}</td>
            <td>{{GPA_4}}</td>
            <td>{{LEVEL_4}}</td>
            <td>{{HOURS_4}}</td>
        </tr>
        <tr>
            <td>5</td>
            <td style="text-align: left;">{{COURSE_5}}</td>
            <td>{{CODE_5}}</td>
            <td>{{CREDIT_5}}</td>
            <td class="grade-excellent">{{SCORE_5}}</td>
            <td>{{GPA_5}}</td>
            <td>{{LEVEL_5}}</td>
            <td>{{HOURS_5}}</td>
        </tr>
        <tr>
            <td>6</td>
            <td style="text-align: left;">{{COURSE_6}}</td>
            <td>{{CODE_6}}</td>
            <td>{{CREDIT_6}}</td>
            <td class="grade-pass">{{SCORE_6}}</td>
            <td>{{GPA_6}}</td>
            <td>{{LEVEL_6}}</td>
            <td>{{HOURS_6}}</td>
        </tr>
    </tbody>
</table>

<div class="summary-box">
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-label">Average GPA</div>
            <div class="summary-value">{{AVERAGE_GPA}}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Credits</div>
            <div class="summary-value">{{TOTAL_CREDITS}}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Class Rank</div>
            <div class="summary-value">{{CLASS_RANK}}</div>
        </div>
    </div>
</div>

<div class="section-title">Academic Performance</div>

<div style="margin: 15px 0;">
    <strong>Overall Rating:</strong>
    <div class="performance-bar">
        <div class="performance-fill" style="width: {{PERFORMANCE_PERCENT}}%;">
            {{PERFORMANCE_RATING}}
        </div>
    </div>
</div>

<div class="evaluation">
    <div class="evaluation-title">Teacher Comments:</div>
    <p>{{TEACHER_COMMENTS}}</p>
</div>

<div class="evaluation" style="background: #e8f5e9; border-left-color: #4caf50;">
    <div class="evaluation-title" style="color: #2e7d32;">Semester Summary:</div>
    <p>{{SEMESTER_SUMMARY}}</p>
</div>

<div class="signature-area">
    <div class="signature-box">
        <div class="signature-line">
            Class Advisor Signature
        </div>
    </div>
    <div class="signature-box">
        <div class="signature-line">
            Academic Affairs Office Seal
        </div>
    </div>
</div>

<div class="seal">
    ✓ Official
</div>

<div class="footer">
    <p><strong>{{UNIVERSITY_NAME}}</strong></p>
    <p>Academic Affairs Office | Tel: (86) 010-12345678 | Email: academic@university.edu.cn</p>
    <p>Issue Date: {{ISSUE_DATE}} | Document ID: TR-{{STUDENT_ID}}-2024</p>
    <p style="margin-top: 10px; font-size: 10px;">This transcript is officially issued and legally binding</p>
</div>

</body>
</html>';

try {
    $db = getDB();
    
    // Find existing transcript template
    $stmt = $db->query("SELECT id FROM templates WHERE name LIKE '%成绩单%' OR name LIKE '%Transcript%' LIMIT 1");
    $template = $stmt->fetch();
    
    $variables = json_encode([
        "STUDENT_NAME", "STUDENT_ID", "CLASS_NAME", "MAJOR", "SEMESTER", 
        "ENROLLMENT_YEAR", "UNIVERSITY_NAME",
        "COURSE_1", "CODE_1", "CREDIT_1", "SCORE_1", "GPA_1", "LEVEL_1", "HOURS_1",
        "COURSE_2", "CODE_2", "CREDIT_2", "SCORE_2", "GPA_2", "LEVEL_2", "HOURS_2",
        "COURSE_3", "CODE_3", "CREDIT_3", "SCORE_3", "GPA_3", "LEVEL_3", "HOURS_3",
        "COURSE_4", "CODE_4", "CREDIT_4", "SCORE_4", "GPA_4", "LEVEL_4", "HOURS_4",
        "COURSE_5", "CODE_5", "CREDIT_5", "SCORE_5", "GPA_5", "LEVEL_5", "HOURS_5",
        "COURSE_6", "CODE_6", "CREDIT_6", "SCORE_6", "GPA_6", "LEVEL_6", "HOURS_6",
        "AVERAGE_GPA", "TOTAL_CREDITS", "CLASS_RANK",
        "PERFORMANCE_PERCENT", "PERFORMANCE_RATING",
        "TEACHER_COMMENTS", "SEMESTER_SUMMARY", "ISSUE_DATE"
    ]);
    
    if ($template) {
        // Update existing template
        $stmt = $db->prepare("UPDATE templates SET content = ?, variables = ? WHERE id = ?");
        $stmt->execute([$newTemplate, $variables, $template['id']]);
        echo "<h2 style='color: green;'>✓ Template updated successfully!</h2>";
        echo "<p>Template ID: {$template['id']}</p>";
    } else {
        // Create new template
        $stmt = $db->prepare("INSERT INTO templates (user_id, name, content, variables, set_type) VALUES (1, 'Student Transcript Enhanced', ?, ?, 'Transcript')");
        $stmt->execute([$newTemplate, $variables]);
        echo "<h2 style='color: green;'>✓ New template created successfully!</h2>";
        echo "<p>Template ID: " . $db->lastInsertId() . "</p>";
    }
    
    echo "<hr>";
    echo "<h3>New template features:</h3>";
    echo "<ul>";
    echo "<li>✓ Gradient header and info bar</li>";
    echo "<li>✓ 6 courses (added 3 more)</li>";
    echo "<li>✓ Detailed course info (code, hours, grade level)</li>";
    echo "<li>✓ Academic performance progress bar</li>";
    echo "<li>✓ Teacher comments</li>";
    echo "<li>✓ Semester summary</li>";
    echo "<li>✓ Signature area</li>";
    echo "<li>✓ Professional footer info</li>";
    echo "<li>✓ Watermark effect</li>";
    echo "</ul>";
    
    echo "<hr>";
    echo "<h3>Next steps:</h3>";
    echo "<p>1. <strong>Refresh the frontend page</strong> (press Ctrl+Shift+R)</p>";
    echo "<p>2. Go to the <strong>Batches</strong> page</p>";
    echo "<p>3. Click <strong>Create Batch</strong></p>";
    echo "<p>4. Select the updated transcript template</p>";
    echo "<p>5. Click <strong>Generate Test Data</strong> to auto-fill test data</p>";
    echo "<p>6. You can also manually edit or add records</p>";
    echo "<p>7. Save the batch and generate PDFs to preview</p>";
    
    echo "<hr>";
    echo "<h3>✨ New features:</h3>";
    echo "<ul>";
    echo "<li><strong>Generate Test Data</strong> - Auto-generate 3 complete test records</li>";
    echo "<li><strong>Clear All</strong> - Clear all records</li>";
    echo "<li>Supports manual editing of every field</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

