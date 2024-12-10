<?php

@include 'config.php';

// Approve product
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE products SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header('Location: AdminProductsApproval.php');
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
    $stmt->close();
}

// Decline product
if (isset($_GET['decline'])) {
    $id = intval($_GET['decline']);
    $stmt = $conn->prepare("UPDATE products SET status = 'declined' WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header('Location: AdminProductsApproval.php');
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
    $stmt->close();
}

// Delete product
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Select image path
    $select_image = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $select_image->bind_param("i", $id);
    $select_image->execute();
    $result = $select_image->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image_path = 'uploads/' . $row['image']; 
        if (file_exists($image_path)) {
            unlink($image_path); // Remove the product image from server
        }
    }

    // Delete product from the database
    $delete_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $delete_stmt->bind_param("i", $id);
    if ($delete_stmt->execute()) {
        header('Location: AdminProductsApproval.php');
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
    $delete_stmt->close();
}

// Fetch pending products
$pending_result = $conn->query("SELECT * FROM products WHERE status = 'pending'");

// Fetch approved products
$approved_result = $conn->query("SELECT * FROM products WHERE status = 'approved'");

// Fetch declined products
$declined_result = $conn->query("SELECT * FROM products WHERE status = 'declined'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Product Approval</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            background-color: #2F5233;
            color: white;
            height: 100vh;
            width: 100px;
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px;
            box-shadow: 2px 0px 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            display: block;
            padding: 15px 0;
            margin-bottom: 10px;
            transition: background-color 0.3s ease;
        }

        .sidebar a:hover {
            background-color: #3b6c4a;
        }

        .sidebar a:active {
            background-color: #2A3D29;
        }

        .sidebar .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .sidebar .logo a {
            color: white;
            text-decoration: none;
        }

        .sidebar .logo a:hover {
            color: #fff;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .container2 {
            margin-left: 240px;
            padding: 20px;
        }

        .section {
            margin-bottom: 30px;
        }

        section h2 {
            background-color: #333;
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 18px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #f4f4f4;
        }

        table td img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        table a {
            color: #2F5233;
            text-decoration: none;
            font-weight: bold;
            padding: 5px 10px;
            border: 2px solid #2F5233;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        table a:hover {
            background-color: #007bff;
            color: white;
        }

        table a:active {
            background-color: #0056b3;
        }

        .product-image {
            cursor: pointer;
            border-radius: 8px;
        }

        #imageModal {
            display: none;
            position: fixed;
            z-index: 1;
            padding-top: 60px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.9);
        }

        #expandedImage {
            margin: auto;
            display: block;
            max-width: 80%;
            max-height: 80%;
        }

        #modalCaption {
            text-align: center;
            color: white;
            padding: 10px;
        }

        #closeModal {
            color: white;
            font-size: 40px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            right: 35px;
            text-shadow: 0 0 3px rgba(255, 255, 255, 0.5);
            cursor: pointer;
        }

        #closeModal:hover,
        #closeModal:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .container2 {
                margin-left: 0;
                width: 100%;
            }

            .section h2 {
                font-size: 16px;
            }

            table th, table td {
                padding: 8px;
                font-size: 14px;
            }

            .product-image {
                width: 80px;
                height: 80px;
            }

            table td img {
                width: 80px;
                height: 80px;
            }
        }

    </style>
</head>
<body>

    <div class="sidebar">
        <a href="AdminCustomerReg.php">Borrower Admin Approval</a>
        <a href="AdminLenderReg.php">Lender Admin Approval</a>
        <a href="AdminProductsApproval.php">Products Admin Approval</a>
        <a href="Admin.php">Back to Dashboard</a>
    </div>

    <h2>Product Admin Approval</h2>
    <div class="container2">

        <!-- Pending Products -->
        <div class="section">
            <h2>Pending Product Registrations</h2>
            <?php if ($pending_result && $pending_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Description</th>
                            <th>Categories</th>
                            <th>Price</th>
                            <th>Shipping Fee</th>
                            <th>Lender Name</th>
                            <th>Location</th>
                            <th>Product Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $pending_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['quantity']); ?></td> 
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo htmlspecialchars($row['categories']); ?></td>
                                <td>₱<?php echo htmlspecialchars($row['price']); ?></td>
                                <td>₱<?php echo htmlspecialchars($row['shippingfee']); ?></td>
                                <td><?php echo htmlspecialchars($row['lender_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td>
                                    <img class="product-image" src="uploaded_img/<?php echo htmlspecialchars($row['image']); ?>" alt="Product Name: <?php echo htmlspecialchars($row['product_name']); ?>" style="width:200px;height:200px;">
                                </td>
                                <td>
                                    <a href="?approve=<?php echo $row['id']; ?>">Approve</a> | 
                                    <a href="?decline=<?php echo $row['id']; ?>">Decline</a> |
                                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No pending product registrations.</p>
            <?php endif; ?>
        </div>

        <!-- Approved Products -->
        <div class="section">
            <h2>Approved Products</h2>
            <?php if ($approved_result && $approved_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Description</th>
                            <th>Categories</th>
                            <th>Price</th>
                            <th>Shipping Fee</th>
                            <th>Lender Name</th>
                            <th>Location</th>
                            <th>Product Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $approved_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo htmlspecialchars($row['categories']); ?></td>
                                <td>₱<?php echo htmlspecialchars($row['price']); ?></td>
                                <td>₱<?php echo htmlspecialchars($row['shippingfee']); ?></td>
                                <td><?php echo htmlspecialchars($row['lender_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td>
                                    <img class="product-image" src="uploaded_img/<?php echo htmlspecialchars($row['image']); ?>" alt="Product: <?php echo htmlspecialchars($row['product_name']); ?>" style="width:100px;height:100px;">
                                </td>
                                <td>
                                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No approved product registrations.</p>
            <?php endif; ?>
        </div>

        <!-- Declined Products -->
        <div class="section">
            <h2>Declined Products</h2>
            <?php if ($declined_result && $declined_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Description</th>
                            <th>Categories</th>
                            <th>Price</th>
                            <th>Shipping Fee</th>
                            <th>Lender Name</th>
                            <th>Location</th>
                            <th>Product Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $declined_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo htmlspecialchars($row['categories']); ?></td>
                                <td>₱<?php echo htmlspecialchars($row['price']); ?></td>
                                <td>₱<?php echo htmlspecialchars($row['shippingfee']); ?></td>
                                <td><?php echo htmlspecialchars($row['lender_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td>
                                    <img class="product-image" src="uploaded_img/<?php echo htmlspecialchars($row['image']); ?>" alt="Product: <?php echo htmlspecialchars($row['product_name']); ?>" style="width:100px;height:100px;">
                                </td>
                                <td>
                                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No declined product registrations.</p>
            <?php endif; ?>
        </div>

    </div>

    <!-- Modal for displaying the image in full size -->
    <div id="imageModal" class="modal">
        <span class="close" id="closeModal">&times;</span>
        <img class="modal-content" id="expandedImage">
        <div id="modalCaption"></div>
    </div>

    <script>
        // Modal functionality
        var modal = document.getElementById("imageModal");
        var modalImg = document.getElementById("expandedImage");
        var captionText = document.getElementById("modalCaption");
        var closeBtn = document.getElementById("closeModal");

        var images = document.querySelectorAll('.product-image');
        images.forEach(function(image) {
            image.onclick = function() {
                modal.style.display = "block";
                modalImg.src = this.src;
                captionText.innerHTML = this.alt;
            };
        });

        closeBtn.onclick = function() {
            modal.style.display = "none";
        };

        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };
    </script>

</body>
</html>

