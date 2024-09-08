<?php
// Database connection
$host = "localhost";
$user = "root"; // Use your MySQL username
$password = ""; // Use your MySQL password
$dbname = "applicant_form"; // Use your database name

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$full_name = $_POST['full_name'];
$mobile_number = $_POST['mobile_number'];
$email = $_POST['email'];
$gender = $_POST['gender'];
$dob = $_POST['dob'];
$father_name = $_POST['father_name'];
$nationality = $_POST['nationality'];
$language = $_POST['language'];
$address = $_POST['address'];
$driving_license = isset($_POST['driving_license']) ? 'Yes' : 'No';
$vehicle_type = isset($_POST['vehicle_type']) ? implode(", ", $_POST['vehicle_type']) : '';
$employment_type = $_POST['employment_type'];
$minimum_salary = $_POST['minimum_salary'];
$needs = isset($_POST['needs']) ? implode(", ", $_POST['needs']) : '';
$academic_qualification = $_POST['academic_qualification'];
$training_skills = $_POST['training_skills'];
$payment_id = $_POST['payment_id'];

// Handle file upload for payment screenshot
$payment_screenshot = $_FILES['payment_screenshot']['name'];
$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES['payment_screenshot']['name']);
move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], $target_file);

// Insert into database
$sql = "INSERT INTO applicants (full_name, mobile_number, email, gender, dob, father_name, nationality, language, address, driving_license, vehicle_type, employment_type, minimum_salary, needs, academic_qualification, training_skills, payment_id, payment_screenshot, terms)
        VALUES ('$full_name', '$mobile_number', '$email', '$gender', '$dob', '$father_name', '$nationality', '$language', '$address', '$driving_license', '$vehicle_type', '$employment_type', '$minimum_salary', '$needs', '$academic_qualification', '$training_skills', '$payment_id', '$payment_screenshot', true)";

if ($conn->query($sql) === TRUE) {
    echo "Record added to database successfully!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Google Sheets integration
require __DIR__ . '/vendor/autoload.php';

putenv('GOOGLE_APPLICATION_CREDENTIALS=path_to_your_credentials.json');

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->addScope(Google_Service_Sheets::SPREADSHEETS);

$service = new Google_Service_Sheets($client);
$spreadsheetId = 'your_google_sheet_id'; // Add your Google Sheets ID
$range = 'Sheet1'; // The sheet name and range to append data

$values = [
    [$full_name, $mobile_number, $email, $gender, $dob, $father_name, $nationality, $language, $address, $driving_license, $vehicle_type, $employment_type, $minimum_salary, $needs, $academic_qualification, $training_skills, $payment_id]
];

$body = new Google_Service_Sheets_ValueRange([
    'values' => $values
]);

$params = [
    'valueInputOption' => 'RAW'
];

$result = $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);

if ($result) {
    echo "Record added to Google Sheets successfully!";
}

$conn->close();
?>
