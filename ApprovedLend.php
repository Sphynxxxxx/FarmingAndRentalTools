<?php
// Include the database connection
include 'config.php'; // Ensure your database connection is correct

// SQL query to fetch approved customers
$sql = "SELECT lender_id, lender_name, contact_number, address, email, images FROM lender WHERE status = 'approved'";

$result = $conn->query($sql);

if (!$result) {
    die("Error executing query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lender's Profile</title>
    <link rel="stylesheet" href="styles.css"> <!-- Optional for styling -->
</head>
<body>
    <h2>Lender's Profilesss</h2>

    <!-- Back Button -->
    <a href="Admin.php">
        <button type="button">Back</button>
    </a>

    <?php if ($result->num_rows > 0): ?>
        <!-- Table displaying the approved customers -->
        <table border="1" cellpadding="10" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Contact Number</th>
                <th>Address</th>
                <th>Email</th>
                <th>Profile Image</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['lender_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['lender_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <?php 
                            // Check if image exists and is a valid image file
                            if ($row['images'] && file_exists('Lender_uploads'.$row['images'])): 
                        ?>
                            <img src="<?php echo htmlspecialchars($row['images']); ?>" alt="Image" style="width:200px;height:200px;">
                        <?php else: ?>
                            <img src="<?php echo htmlspecialchars($row['images']); ?>" alt="Image" style="width:200px;height:200px;">
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No approved customers found.</p>
    <?php endif; ?>

    <?php $conn->close(); ?>
</body>
</html>
