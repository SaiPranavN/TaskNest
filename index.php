<?php
include 'db_connect.php';

// Fetch services for the dropdown
$services_sql = "SELECT * FROM services";
$services_result = $conn->query($services_sql);

// Fetch filtered and sorted service providers
$service_id_filter = isset($_GET['service_id']) ? $_GET['service_id'] : 0;
$city_filter = isset($_GET['city']) ? $_GET['city'] : '';
$sort_option = isset($_GET['sort']) ? $_GET['sort'] : 'rating';

$providers_sql = "SELECT sp.provider_id, sp.name, sp.phone_number, sp.city_name, sp.rating, sp.rate_per_hour, s.service_name 
                  FROM service_providers sp
                  JOIN services s ON sp.service_id = s.service_id
                  WHERE 1=1";
if ($service_id_filter > 0) {
    $providers_sql .= " AND sp.service_id = $service_id_filter";
}
if (!empty($city_filter)) {
    $providers_sql .= " AND sp.city_name LIKE '%$city_filter%'";
}
if ($sort_option == 'rate_per_hour') {
    $providers_sql .= " ORDER BY sp.rate_per_hour ASC";
} else {
    $providers_sql .= " ORDER BY sp.rating DESC";
}
$providers_result = $conn->query($providers_sql);


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_provider'])) {
    $name = $_POST['name'];
    $phone_number = $_POST['phone_number'];
    $city_name = $_POST['city_name'];
    $rating = $_POST['rating'];
    $rate_per_hour = $_POST['rate_per_hour'];
    $service_id = $_POST['service_id'];

    $sql = "INSERT INTO service_providers (name, phone_number, city_name, rating, rate_per_hour, service_id) 
            VALUES ('$name', '$phone_number', '$city_name', $rating, $rate_per_hour, $service_id)";
    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_provider'])) {
    $provider_id = $_POST['provider_id'];

    $sql = "DELETE FROM service_providers WHERE provider_id = $provider_id";
    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Service Providers</title>
</head>
<body>
    <div class="container">
        <h1>Service Providers' Database Record</h1>

        <button id="openAddModal" class="button">Add New Service Provider</button>
        <button id="openDeleteModal" class="button">Delete Service Provider</button>

        <form method="get" action="index.php" class="filter-form">
            <div class="form-group full-width">
                <label for="service_id">Filter by Service:</label>
                <select id="service_id" name="service_id">
                    <option value="0">All Services</option>
                    <?php while($row = $services_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['service_id']; ?>" <?php if ($service_id_filter == $row['service_id']) echo 'selected'; ?>>
                            <?php echo $row['service_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group full-width">
                <label for="city">Filter by City:</label>
                <input type="text" id="city" name="city" value="<?php echo $city_filter; ?>">
            </div>
            <button type="submit" style="background-color:darkgreen">Filter</button>
            <button type="submit" name="sort" value="rating" class="sorting-button">Sort by Rating</button>
            <button type="submit" name="sort" value="rate_per_hour" class="sorting-button">Sort by Rate per Hour</button>
        </form>
        
        <div id="table_container">
            <table>
                <tr>
                    <th>Provider ID</th>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>City Name</th>
                    <th>Rating</th>
                    <th>Rate per Hour</th>
                    <th>Service</th>
                </tr>
                <?php while($row = $providers_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['provider_id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['phone_number']; ?></td>
                        <td><?php echo $row['city_name']; ?></td>
                        <td><?php echo $row['rating']; ?></td>
                        <td><?php echo $row['rate_per_hour']; ?></td>
                        <td><?php echo $row['service_name']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        

        <!-- Add Modal -->
        <div id="addModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeAddModal">&times;</span>
                <h2>Add New Service Provider</h2>
                <form method="post" action="index.php" class="add-form">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number:</label>
                        <input type="text" id="phone_number" name="phone_number" required>
                    </div>
                    <div class="form-group">
                        <label for="city_name">City Name:</label>
                        <input type="text" id="city_name" name="city_name" required>
                    </div>
                    <div class="form-group">
                        <label for="rating">Rating:</label>
                        <input type="number" id="rating" name="rating" min="0" max="5" step="0.1" required>
                    </div>
                    <div class="form-group">
                        <label for="rate_per_hour">Rate per Hour:</label>
                        <input type="number" id="rate_per_hour" name="rate_per_hour" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="service_id">Service:</label>
                        <select id="service_id" name="service_id">
                            <?php
                            $services_result->data_seek(0); // Reset pointer to beginning
                            while($row = $services_result->fetch_assoc()): ?>
                                <option value="<?php echo $row['service_id']; ?>">
                                    <?php echo $row['service_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" name="add_provider">Add Provider</button>
                </form>
            </div>
        </div>

        <!-- Delete Modal -->
        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeDeleteModal">&times;</span>
                <h2>Delete Service Provider</h2>
                <form method="post" action="index.php" class="delete-form">
                    <div class="form-group full-width">
                        <label for="provider_id">Provider ID:</label>
                        <input type="number" id="provider_id" name="provider_id" required>
                    </div>
                    <button type="submit" name="delete_provider">Delete Provider</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        
        var addModal = document.getElementById('addModal');
        var deleteModal = document.getElementById('deleteModal');

        
        var openAddModal = document.getElementById('openAddModal');
        var openDeleteModal = document.getElementById('openDeleteModal');

        
        var closeAddModal = document.getElementById('closeAddModal');
        var closeDeleteModal = document.getElementById('closeDeleteModal');

        addModal.style.display = "none";
        deleteModal.style.display = "none";

        

        

        
        openAddModal.onclick = function() {
            addModal.style.display = "block";
        }
        openDeleteModal.onclick = function() {
            deleteModal.style.display = "block";
        }

        
        closeAddModal.onclick = function() {
            addModal.style.display = "none";
        }
        closeDeleteModal.onclick = function() {
            deleteModal.style.display = "none";
        }

        
        window.onclick = function(event) {
            if (event.target == addModal) {
                addModal.style.display = "none";
            }
            if (event.target == deleteModal) {
                deleteModal.style.display = "none";
            }
        }
    </script>
</body>
</html>
