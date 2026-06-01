<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $stock = intval($_POST['stock']);
    $price = floatval($_POST['price']);
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : NULL;

    // Initialize image variables
    $image_path = NULL;

    // Handle image upload if an image is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = basename($_FILES['image']['name']); // Just the file name
        $image_tmp = $_FILES['image']['tmp_name'];
        $imageFileType = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($imageFileType, $valid_extensions)) {
            die("Invalid image type. Allowed types: jpg, jpeg, png, gif.");
        }

        // Set the image path for moving the file
        $upload_path = "uploads/" . $image;

        // Move uploaded image to the 'uploads' folder
        if (!move_uploaded_file($image_tmp, $upload_path)) {
            echo "Error uploading image.";
            exit();
        }

        $image_path = $image; // Store only the filename in the database
    }

    // Prepare and execute insert query
    $stmt = $conn->prepare("INSERT INTO products (name, category, stock, price, expiry_date, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssidss", $name, $category, $stock, $price, $expiry_date, $image_path);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Database error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #d3c4a0, #f7f2e8);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 30px 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-container {
            background-color: #ffffff;
            padding: 40px 35px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 650px;
            width: 90%;
            margin: 0 auto;
            transition: all 0.3s ease-in-out;
            position: relative;
            overflow: hidden;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #c2b280, #d3c4a0, #c2b280);
        }

        .add-title-img {
            display: block;
            margin: 0 auto 30px;
            max-width: 280px;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
            transition: transform 0.3s ease;
        }

        .add-title-img:hover {
            transform: scale(1.03);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
            color: #1e2a38;
            position: relative;
            padding-bottom: 12px;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background-color: #c2b280;
            border-radius: 3px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #324154;
            display: block;
            font-size: 15px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            font-size: 15px;
            border: 1px solid #e0e0e0;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: all 0.25s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(194, 178, 128, 0.15);
            border-color: #c2b280;
        }

        .input-group-text {
            background-color: #f8f8f8;
            border: 1px solid #e0e0e0;
            border-right: none;
            border-radius: 12px 0 0 12px;
            padding: 0 15px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }

        .btn {
            width: 100%;
            border-radius: 12px;
            padding: 14px;
            font-weight: 500;
            font-size: 16px;
            transition: all 0.25s ease;
            margin-bottom: 12px;
        }

        .btn-success {
            background: linear-gradient(145deg, #c2b280, #d3c4a0);
            border: none;
            color: white;
            box-shadow: 0 4px 10px rgba(194, 178, 128, 0.3);
        }

        .btn-success:hover {
            background: linear-gradient(145deg, #d3c4a0, #c2b280);
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(194, 178, 128, 0.4);
        }

        .btn-success:active {
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(194, 178, 128, 0.3);
        }

        .back-btn {
            background-color: #f2f2f2;
            border: none;
            color: #4a5568;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .back-btn:hover {
            background-color: #e8e8e8;
            color: #2d3748;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
        }

        .back-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .icon {
            color: #c2b280;
            font-size: 1.1rem;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23c2b280' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
        }

        .form-floating {
            position: relative;
        }

        .form-floating label {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            padding: 12px 15px;
            pointer-events: none;
            border: 1px solid transparent;
            transform-origin: 0 0;
            transition: all 0.2s ease;
            color: #6c757d;
        }

        .form-floating .form-control:focus ~ label,
        .form-floating .form-control:not(:placeholder-shown) ~ label {
            transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
            background-color: white;
            padding: 0 5px;
            color: #c2b280;
            height: auto;
        }

        .form-floating .form-control {
            padding-top: 22px;
            padding-bottom: 8px;
        }

        .file-upload {
            position: relative;
            display: block;
            width: 100%;
        }

        .file-upload .form-control {
            padding-left: 42px;
        }

        .file-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #c2b280;
            font-size: 18px;
            z-index: 5;
        }

        .form-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 6px;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section-title {
            color: #2d3748;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #edf2f7;
        }

        @media (max-width: 767px) {
            .form-container {
                padding: 30px 20px;
                border-radius: 16px;
            }
            
            .add-title-img {
                max-width: 220px;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <img src="images/addproduct.png" alt="Add Product" class="add-title-img">
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-section">
                <div class="form-section-title">Product Information</div>
                
                <div class="form-group form-floating">
                    <input type="text" id="name" name="name" class="form-control" placeholder=" " required>
                    <label for="name">Product Name</label>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="" disabled selected>Select a category</option>
                        <option value="Food">Food</option>
                        <option value="Toy">Toy</option>
                        <option value="Accessory">Accessory</option>
                        <option value="Health & Wellness">Health & Wellness</option>
                        <option value="Grooming">Grooming</option>
                    </select>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">Inventory Details</div>
                
                <div class="form-group form-floating">
                    <input type="number" id="stock" name="stock" class="form-control" placeholder=" " min="1" required>
                    <label for="stock">Stock Quantity</label>
                </div>

                <div class="form-group">
                    <label for="price">Price (₱)</label>
                    <div class="input-group">
                        <div class="input-group-text">
                            <i class="fas fa-tags icon"></i>
                        </div>
                        <input type="text" id="price" name="price" class="form-control" placeholder="0.00" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="expiry_date">Expiry Date (Optional)</label>
                    <div class="input-group">
                        <div class="input-group-text">
                            <i class="fas fa-calendar-day icon"></i>
                        </div>
                        <input type="date" id="expiry_date" name="expiry_date" class="form-control">
                    </div>
                    <small class="form-text">Leave empty if product doesn't expire</small>
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-title">Product Image</div>
                
                <div class="form-group">
                    <div class="file-upload">
                        <i class="fas fa-image file-icon"></i>
                        <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/gif">
                    </div>
                    <small class="form-text">Supported formats: JPG, JPEG, PNG, GIF (Max 5MB)</small>
                </div>
            </div>

            <button type="submit" class="btn btn-success">
                <i class="fas fa-plus-circle me-2"></i> Add Product
            </button>
            <a href="index.php" class="btn back-btn">
                <i class="fas fa-arrow-left me-2"></i> Back to Products
            </a>
        </form>
    </div>

    <script>
        // Add simple validation and preview for image upload
        document.getElementById('image').addEventListener('change', function(e) {
            const fileSize = this.files[0].size / 1024 / 1024; // in MB
            if (fileSize > 5) {
                alert('File size exceeds 5MB. Please choose a smaller image.');
                this.value = '';
            }
        });
    </script>
</body>
</html>