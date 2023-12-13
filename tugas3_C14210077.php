<?php

require_once 'autoload.php';

$client = new MongoDB\Client("mongodb://localhost:27017");
$collection = $client->emilyjg->restaurants;

$restaurants = []; #fetch data
$filter = []; #for filter


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $borough = $_POST['borough'] ?? '';
    $cuisine = $_POST['cuisine'] ?? '';
    $lastGrade = (float) ($_POST['grade'] ?? 0);


    if ($borough !== '') {
        $filter['borough'] = $borough;
    }

    if ($cuisine !== '') {
        $filter['cuisine'] = ['$regex' => $cuisine, '$options' => 'i'];
    }

    if ($lastGrade > 0) {
        $filter['grades.0.score'] = ['$lt' => $lastGrade];
    }

    if (!empty($filter)) {
        $filteredCursor = $collection->find($filter, ['projection' => ['_id' => 0]]);
        $restaurants = iterator_to_array($filteredCursor);
    }
} else {
    $restaurants = $collection->find([]);
}
?>



<!DOCTYPE html>
<html>

<head>
    <title>Restaurants</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lexend&display=swap');

        * {
            font-family: 'Lexend', sans-serif !important
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }

        th{
            background-color: darksalmon !important;

        }
        th,
        td {
            text-align: center;
            padding: 8px;
            width: 12.5%;
            overflow: hidden;
            word-wrap: break-word;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .row {
            margin-bottom: 10px;
        }

        .table.rounded {
            border-radius: 10px;
            overflow: hidden;
            background-color: cadetblue;
        }

        .thead {
            background-color: darksalmon;
            border-style: solid;
        }

        .h1 {
            text-align: center;
            margin-top: 10px;
        }
        .word-wrap{
            word-wrap:break-word;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class=h1>🍱 Restaurants 🍜</h1>

    </div>
    <div class="container">
        
        <form id="filterForm" method="post">

            <div class="row mb-3">
                <div class="col">

                    <select class="form-select" aria-label="Borough" id="borough" name="borough">
                        <option selected disabled>Borough</option>
                        <?php
                        $boroughs = $collection->distinct('borough');
                        foreach ($boroughs as $borough) {
                            echo "<option value='$borough'>$borough</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col">
                    <input class="form-control" type="text" id="cuisine" name="cuisine" placeholder="Cuisine">

                </div>

                <div class="col">
                    <input class="form-control" type="number" id="grade" name="grade" placeholder="Last Grade Score">
                </div>

                <div class="col">
                    <button class="btn" style="background-color:darksalmon" id="filter" type="submit">Filter</button>
                </div>


            </div>

        </form>

    </div>

    <div class="container">

        <table id="restaurantsTable" class="table table-bordered rounded">
            <thead class="thead-colored">
                <tr>
                    <th>Restaurant Id</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Borough</th>
                    <th>Cuisine</th>
                    <th>Last Grade</th>
                    <th>Last Score</th>

                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($restaurants as $restaurant) {
                    echo "<tr>";
                    echo "<td>" . $restaurant['restaurant_id'] . "</td>";
                    echo "<td class='word-wrap'>" . $restaurant['name'] . "</td>";
                    echo "<td class='word-wrap'>" . $restaurant['address']['building'] . " " . $restaurant['address']['street'] . "</td>";
                    echo "<td>" . $restaurant['borough'] . "</td>";
                    echo "<td>" . $restaurant['cuisine'] . "</td>";
                    echo "<td>" . $restaurant['grades'][0]['grade'] . "</td>";
                    echo "<td>" . $restaurant['grades'][0]['score'] . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

    </div>
    <script>
        $(document).ready(function () {

            $('#filter').submit(function (event) {
                event.preventDefault();
                var borough = $('#borough').val();
                var cuisine = $('#cuisine').val();
                var grade = $('#grade').val();

                $.ajax({
                    type: 'post',
                    data: {
                        borough: borough,
                        cuisine: cuisine,
                        grade: grade
                    },
                    success: function (data) {
                        $('#restaurantsTable tbody').empty();

                        $.each(data, function (index, restaurant) {
                            var newRow = '<tr>' +
                                '<td>' + restaurant.restaurant_id + '</td>' +
                                '<td>' + restaurant.name + '</td>' +
                                '<td>' + restaurant.address + '</td>' +
                                '<td>' + restaurant.borough + '</td>' +
                                '<td>' + restaurant.cuisine + '</td>' +
                                '<td>' + restaurant.grades + '</td>' +
                                '<td>' + restaurant.score + '</td>' +
                                '</tr>';

                            $('#restaurantsTable tbody').append(newRow);
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        });

    </script>
</body>

</html>