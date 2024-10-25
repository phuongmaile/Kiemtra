<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">PHP Example</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
                    aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                    <div class="navbar-nav">
                        <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <div class="container my-3">
        <nav class="alert alert-primary" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Index</li>
            </ol>
        </nav>

        <form class="row" method="POST" enctype="multipart/form-data">
            <div class="col">
                <div class="mb-3">
                    <input type="file" accept=".txt" class="form-control" name="file" required>
                </div>
                <button type="submit" class="btn btn-primary" name="submit">Import database</button>
            </div>
            <div class="col">
            </div>
        </form>

        <?php
        if (isset($_POST['submit'])) {
            if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                $fileTmpPath = $_FILES['file']['tmp_name'];
                $fileName = $_FILES['file']['name'];
                $fileType = $_FILES['file']['type'];

                if ($fileType == 'text/plain') {
                    // 1. Read data from the text file
                    $file = fopen($fileTmpPath, "r");
                    $successCount = 0;
                    $failureCount = 0;

                    $data = [];
                    $currentRecord = [];

                    while (($line = fgets($file)) !== false) {
                        $line = trim($line);

                        if (strpos($line, 'ID:') === 0) {
                            if (!empty($currentRecord)) {
                                $data[] = $currentRecord;
                                $currentRecord = []; 
                            }
                        } elseif (strpos($line, 'Title:') === 0) {
                            $currentRecord['Title'] = substr($line, 7); 
                        } elseif (strpos($line, 'Description:') === 0) {
                            $currentRecord['Description'] = substr($line, 13); 
                        } elseif (strpos($line, 'Image URL:') === 0) {
                            $currentRecord['ImageUrl'] = substr($line, 10); 
                        }
                    }
                    if (!empty($currentRecord)) {
                        $data[] = $currentRecord;
                    }

                    fclose($file);

                    // 2. Insert data into the database
                    $server = "localhost";
                    $database = "db_le_mai_phuong"; 
                    $username = "root";
                    $password = "";

                    $conn = new mysqli($server, $username, $password, $database);
                    if ($conn->connect_error) {
                        die('Connect failed: ' . $conn->connect_error);
                    }

                    foreach ($data as $record) {
                        $title = $conn->real_escape_string($record['Title']);
                        $description = $conn->real_escape_string($record['Description']);
                        $imageUrl = $conn->real_escape_string($record['ImageUrl']);

                        // Check if Title already exists
                        $checkQuery = "SELECT COUNT(*) as count FROM Course WHERE Title = '$title'";
                        $result = $conn->query($checkQuery);
                        $row = $result->fetch_assoc();

                        if ($row['count'] == 0) {
                            $insertQuery = "INSERT INTO Course (Title, Description, ImageUrl) VALUES ('$title', '$description', '$imageUrl')";
                            if ($conn->query($insertQuery) === TRUE) {
                                $successCount++;
                            } else {
                                $failureCount++;
                            }
                        } else {
                            $failureCount++; 
                        }
                    }

                    $conn->close();

                    // 3. Display the number of records inserted successfully and failed
                    echo '<div class="alert alert-info mt-2" role="alert">
                            ' . $successCount . ' records inserted successfully, ' . $failureCount . ' records inserted failed.
                          </div>';
                } else {
                    echo '<div class="alert alert-warning" role="alert">
                            Please upload a .txt file!
                          </div>';
                }
            } else {
                echo '<div class="alert alert-danger" role="alert">
                        File upload error!
                      </div>';
            }
        }
        ?>

        <hr>

        <?php
        // Displaying the records from the database
        $server = "localhost";
        $database = "db_le_mai_phuong"; 
        $username = "root";
        $password = "";

        $conn = new mysqli($server, $username, $password, $database);
        if ($conn->connect_error) {
            die('Connect failed: ' . $conn->connect_error);
        }

        $query = "SELECT * FROM Course";
        $result = $conn->query($query);

        echo '<div class="row row-cols-1 row-cols-md-2 g-4">';
        if ($result->num_rows > 0) {
            while ($course = $result->fetch_assoc()) {
        ?>
                <div class="col">
                    <div class="card">
                        <img src="<?= htmlspecialchars($course['ImageUrl']) ?>" class="card-img-top" alt="<?= htmlspecialchars($course['Title']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($course['Title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($course['Description']) ?></p>
                        </div>
                    </div>
                </div>
        <?php
            }
        }
        echo ' </div>';

        $conn->close();
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>
