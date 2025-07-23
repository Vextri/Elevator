<?php
    //grab the diagnostics data from the JSON file
    $str = file_get_contents('../json/diagnostics.json');
    $json = json_decode($str, true);
    //echo '<pre>' . print_r($json, true) . '</pre>';

    // find each floor's set of measurements, and put them in their own arrays
    $floor1_data = isset($json['floor1']) ? $json['floor1'] : [];
    $floor2_data = isset($json['floor2']) ? $json['floor2'] : [];
    $floor3_data = isset($json['floor3']) ? $json['floor3'] : [];
    $timestamp = isset($json['timestamp']) ? $json['timestamp'] : '';

    // sort the array from lowest to highest value
    sort($floor1_data);
    sort($floor2_data); 
    sort($floor3_data);

    $total_numMeasurements = 30;
    $floor1_setpoint = 350;
    $floor2_setpoint = 635;
    $floor3_setpoint = 1220;

?>


<html lang="en">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elevator Height Diagnostics</title>
</head>
<body>
    <header>
        <h1 style="text-align:center;">Height Diagnostics</h1>
        <h2><?php echo "Timestamp: $timestamp"; ?></h2>
    </header>
    <div>
        <canvas id="floor1" width="700" height="100">Floor 1</canvas>
        <canvas id="floor2" width="700" height="100">Floor 2</canvas>
        <canvas id="floor3" width="700" height="100">Floor 3</canvas>
        <script>
            const floor1Data = <?php echo json_encode($floor1_data); ?>;
            const floor2Data = <?php echo json_encode($floor2_data); ?>;
            const floor3Data = <?php echo json_encode($floor3_data); ?>;

            const ctx1 = document.getElementById('floor1').getContext('2d');
            const ctx2 = document.getElementById('floor2').getContext('2d');
            const ctx3 = document.getElementById('floor3').getContext('2d');

            const chart1 = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: floor1Data.map((_, i) => i + 1), // generates labels like "1", "2", "3", ...
                    datasets: [{
                        label: 'Floor 1',
                        data: floor1Data,
                        borderColor: 'pink',
                        borderWidth: 2,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Measurement'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Height'
                            }
                        }
                    }
                }
            });

            const chart2 = new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: floor2Data.map((_, i) => i + 1),
                    datasets: [{
                        label: 'Floor 2',
                        data: floor2Data,
                        borderColor: 'purple',  
                        borderWidth: 2,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Measurement'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Height'
                            }
                        }
                    }
                }
            });

            const chart3 = new Chart(ctx3, {
                type: 'line',
                data: {
                    labels: floor3Data.map((_, i) => i + 1),
                    datasets: [{
                        label: 'Floor 3',
                        data: floor3Data,
                        borderColor: 'blue',
                        borderWidth: 2,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Measurement'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Height'
                            }
                        }
                    }
                }
            });

        </script>

    </div>

    <a href="../index.php">Go Back</a>
</body>
</html>
 
 
