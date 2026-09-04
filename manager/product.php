<?php
require_once "../config/db.php";
require_once "../includes/functions.php";
require_once "includes/manager_functions.php";
manager_require_login();
manager_ensure_stock_image_column($pdo);

$stock_id = trim($_GET["id"] ?? "");
$is_edit = $stock_id !== "";
$product = [
    "lpa_stock_ID" => "",
    "lpa_stock_name" => "",
    "lpa_stock_desc" => "",
    "lpa_stock_onhand" => "0",
    "lpa_stock_price" => "0.00",
    "lpa_stock_status" => "E",
    "lpa_stock_image" => ""
];
$message = "";

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM lpa_stock WHERE lpa_stock_ID = ?");
    $stmt->execute([$stock_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        die("Product not found.");
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    manager_check_csrf();
    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = filter_var($_POST["price"] ?? null, FILTER_VALIDATE_FLOAT);
    $quantity = filter_var($_POST["quantity"] ?? null, FILTER_VALIDATE_INT);
    $status = ($_POST["status"] ?? "") === "D" ? "D" : "E";
    $image_name = $product["lpa_stock_image"] ?? "";

    if ($name === "" || $description === "") {
        $message = "<div class='error'>Product name and description are required.</div>";
    } elseif ($price === false || $price < 0 || $price > 99999.99) {
        $message = "<div class='error'>Enter a valid product price.</div>";
    } elseif ($quantity === false || $quantity < 0 || $quantity > 99999) {
        $message = "<div class='error'>Enter a valid stock quantity.</div>";
    } else {
        if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
            $upload = $_FILES["product_image"];

            if ($upload["error"] !== UPLOAD_ERR_OK) {
                $message = "<div class='error'>The image upload failed.</div>";
            } elseif ($upload["size"] > 5 * 1024 * 1024) {
                $message = "<div class='error'>The image must be smaller than 5 MB.</div>";
            } else {
                $allowed_types = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];
                $mime_type = (new finfo(FILEINFO_MIME_TYPE))->file($upload["tmp_name"]);

                if (!isset($allowed_types[$mime_type])) {
                    $message = "<div class='error'>Use a JPG, PNG or WebP image.</div>";
                } else {
                    $upload_directory = __DIR__ . "/../assets/images/products";
                    if (!is_dir($upload_directory)) {
                        mkdir($upload_directory, 0755, true);
                    }
                    $image_name = "products/" . bin2hex(random_bytes(12)) . "." . $allowed_types[$mime_type];
                    $destination = __DIR__ . "/../assets/images/" . $image_name;

                    if (!move_uploaded_file($upload["tmp_name"], $destination)) {
                        $message = "<div class='error'>The image could not be saved.</div>";
                    }
                }
            }
        }

        if ($message === "") {
            if ($is_edit) {
                $stmt = $pdo->prepare("UPDATE lpa_stock SET lpa_stock_name = ?, lpa_stock_desc = ?, lpa_stock_onhand = ?, lpa_stock_price = ?, lpa_stock_status = ?, lpa_stock_image = ? WHERE lpa_stock_ID = ?");
                $stmt->execute([$name, $description, $quantity, $price, $status, $image_name, $stock_id]);
                lpa_log("Manager updated product: " . $stock_id);
                manager_set_message("success", "Product updated successfully.");
            } else {
                $next_number = (int)$pdo->query("SELECT COALESCE(MAX(CAST(SUBSTRING(lpa_stock_ID, 4) AS UNSIGNED)), 0) + 1 FROM lpa_stock WHERE lpa_stock_ID LIKE 'STK%'")->fetchColumn();
                $stock_id = "STK" . str_pad($next_number, 3, "0", STR_PAD_LEFT);
                $stmt = $pdo->prepare("INSERT INTO lpa_stock (lpa_stock_ID, lpa_stock_name, lpa_stock_desc, lpa_stock_onhand, lpa_stock_price, lpa_stock_status, lpa_stock_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$stock_id, $name, $description, $quantity, $price, $status, $image_name]);
                lpa_log("Manager created product: " . $stock_id);
                manager_set_message("success", "Product created successfully.");
            }

            header("Location: index.php");
            exit;
        }
    }

    $product["lpa_stock_name"] = $name;
    $product["lpa_stock_desc"] = $description;
    $product["lpa_stock_onhand"] = $quantity === false ? "" : $quantity;
    $product["lpa_stock_price"] = $price === false ? "" : $price;
    $product["lpa_stock_status"] = $status;
    $product["lpa_stock_image"] = $image_name;
}

include "includes/manager_header.php";
?>
<section class="manager-title-row compact-manager-title">
    <div>
        <a class="back-link" href="index.php">← Back to products</a>
        <span class="eyebrow"><?php echo $is_edit ? "Update catalogue item" : "New catalogue item"; ?></span>
        <h1><?php echo $is_edit ? "Edit product" : "Add product"; ?></h1>
    </div>
</section>

<div class="manager-editor">
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(manager_csrf_token()); ?>">
        <?php echo $message; ?>
        <label>Product Name</label>
        <input type="text" name="name" maxlength="250" value="<?php echo htmlspecialchars($product["lpa_stock_name"]); ?>" required>

        <label>Description</label>
        <textarea name="description" rows="4" required><?php echo htmlspecialchars($product["lpa_stock_desc"]); ?></textarea>

        <div class="form-grid">
            <div><label>Price ($)</label><input type="number" name="price" min="0" max="99999.99" step="0.01" value="<?php echo htmlspecialchars($product["lpa_stock_price"]); ?>" required></div>
            <div><label>Stock Quantity</label><input type="number" name="quantity" min="0" max="99999" value="<?php echo htmlspecialchars($product["lpa_stock_onhand"]); ?>" required></div>
        </div>

        <label>Catalogue Status</label>
        <select name="status">
            <option value="E" <?php echo $product["lpa_stock_status"] === "E" ? "selected" : ""; ?>>Enabled</option>
            <option value="D" <?php echo $product["lpa_stock_status"] === "D" ? "selected" : ""; ?>>Disabled</option>
        </select>

        <label>Product Image</label>
        <div class="manager-image-editor">
            <?php if (!empty($product["lpa_stock_image"])): ?>
                <img src="../assets/images/<?php echo htmlspecialchars($product["lpa_stock_image"]); ?>" alt="Current product image">
            <?php else: ?><div class="no-image-preview">No image selected</div><?php endif; ?>
            <div><input type="file" name="product_image" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG or WebP. Maximum 5 MB.</small></div>
        </div>

        <div class="form-actions">
            <button type="submit"><?php echo $is_edit ? "Save changes" : "Create product"; ?></button>
            <a class="button secondary-button" href="index.php">Cancel</a>
        </div>
    </form>
</div>
<?php include "includes/manager_footer.php"; ?>
