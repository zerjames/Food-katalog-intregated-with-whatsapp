<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
	header('Location: login.php');
	exit;
}

require __DIR__ . '/../inc/db.php';

$excel_data = export_sales_to_excel();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="sales_export_' . date('Y-m-d_H-i-s') . '.xls"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

echo $excel_data;
exit;
?>
