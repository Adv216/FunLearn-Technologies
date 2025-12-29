<?php
include 'secure_page_template.php';
include 'db_connect.php';

check_permission([ROLE_ADMIN, ROLE_MANAGER]);

$month = $_GET['month'] ?? date('m');
$year  = $_GET['year'] ?? date('Y');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="salary_report_'.$month.'_'.$year.'.csv"');

$output = fopen("php://output", "w");

/* CSV Header */
fputcsv($output, [
    'Employee Name',
    'Days Worked',
    'Total Hours',
    'Hourly Rate',
    'Salary'
]);

$sql = "
SELECT
    e.Employee_Name,
    COUNT(DISTINCT a.Attendance_Date) AS Days_Worked,
    SUM(a.Working_Hours) AS Total_Working_Hours,
    e.Hourly_Rate,
    ROUND(SUM(a.Working_Hours) * e.Hourly_Rate, 2) AS Salary
FROM EMPLOYEE_ATTENDANCE a
JOIN EMPLOYEES e ON a.Employee_ID = e.Employee_ID
WHERE
    MONTH(a.Attendance_Date) = ?
    AND YEAR(a.Attendance_Date) = ?
GROUP BY e.Employee_ID
ORDER BY e.Employee_Name
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['Employee_Name'],
        $row['Days_Worked'],
        number_format($row['Total_Working_Hours'], 2),
        number_format($row['Hourly_Rate'], 2),
        number_format($row['Salary'], 2)
    ]);
}

fclose($output);
exit;
