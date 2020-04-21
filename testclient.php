<?php

require_once('config.php');

//$resource = 'https://team.moodle.net/uploads/01E394TX0TXVCQ1DN0D48JKBRT/cat.png';
$resource = 'https://team.moodle.net/uploads/01E1GPC7V8EV829WBPTW64ZN4B/backup-moodle2-course-2-course_1-20190909-1514.mbz';

echo '
<html>
<head>
</head>
<body>
<br>
<h3>A list of MoodleNet resources as might be seen there:</h3>
<br>
';

// Valid case - no course or section.
echo '
<div style="border: solid 1px #bbbbbb; padding: 10px;">
<p>
Course and section: omitted<br>
POST data: valid
</p>
<form name="testForm" id="testForm" action="admin/tool/moodlenet/import.php" method="post">
    <input type="hidden" name="resourceurl" value="'.$resource.'"/>
    <input type="submit" value="Send to Moodle">
</form>
</div><br>';

// Valid case - course and section provided.
echo '
<div style="border: solid 1px #bbbbbb; padding: 10px;">
<p>
Course and section: included as import url params<br>
POST data: valid
</p>
<form name="testForm" id="testForm" action="admin/tool/moodlenet/import.php?course=5&section=1" method="post">
    <input type="hidden" name="resourceurl" value="'.$resource.'"/>
    <input type="submit" value="Send to Moodle">
</form>
</div><br>';

// Invalid case - POST data missing resourceurl param.
echo '
<div style="border: solid 1px #bbbbbb; padding: 10px;">
<p>
POST data: invalid</p>
<form action="admin/tool/moodlenet/import.php" method="post">
    <input type="hidden" name="broken" value="'.$resource.'"/>
    <input type="submit" value="Send to Moodle">
</form>
</div>';

echo '
</div>
</body>
</html>'; 