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

    //setpoints for each floor (mm)
    $floor1_setpoint = 350;
    $floor2_setpoint = 635;
    $floor3_setpoint = 1220;

    // create arrays with repeated setpoint values
    $floor1_setpoint_array = array_fill(0, count($floor1_data), $floor1_setpoint);
    $floor2_setpoint_array = array_fill(0, count($floor2_data), $floor2_setpoint);
    $floor3_setpoint_array = array_fill(0, count($floor3_data), $floor3_setpoint);

    // sort the floor data (diagnostics) array from lowest to highest value
    sort($floor1_data);
    sort($floor2_data); 
    sort($floor3_data);

    $total_numMeasurements = 30;


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

            const floor1Setpoint = <?php echo json_encode($floor1_setpoint_array); ?>;
            const floor2Setpoint = <?php echo json_encode($floor2_setpoint_array); ?>;
            const floor3Setpoint = <?php echo json_encode($floor3_setpoint_array); ?>;

            const ctx1 = document.getElementById('floor1').getContext('2d');
            const ctx2 = document.getElementById('floor2').getContext('2d');
            const ctx3 = document.getElementById('floor3').getContext('2d');

            const chart1 = new Chart(ctx1, {
                type: 'line',
                    data: {
                        labels: floor1Data.map((_, i) => i + 1), // Use only one set of labels
                        datasets: [{
                            label: 'Floor 1',
                            data: floor1Data,
                            borderColor: 'pink',
                            borderWidth: 2,
                            fill: false
                        }, // ✅ Added comma
                        {
                            label: 'Setpoint',
                            data: floor1Setpoint, // This will create a horizontal line at 350
                            borderColor: 'red',
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
                                text: 'Height (mm)'
                            },
                            min: 0,
                            max: 400
                        }
                    }
                }
            });

            const chart2 = new Chart(ctx2, {
                type: 'line',
                data: {
                        labels: floor2Data.map((_, i) => i + 1), // Use only one set of labels
                        datasets: [{
                            label: 'Floor 2',
                            data: floor2Data,
                            borderColor: 'lightblue',
                            borderWidth: 2,
                            fill: false
                        }, // ✅ Added comma
                        {
                            label: 'Setpoint',
                            data: floor2Setpoint, // This will create a horizontal line at 350
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
                                text: 'Height (mm)'
                            },
                            min: 0,
                            max: 700,
                        }
                    }
                }
            });

            const chart3 = new Chart(ctx3, {
                type: 'line',
                data: {
                        labels: floor3Data.map((_, i) => i + 1), // Use only one set of labels
                        datasets: [{
                            label: 'Floor 3',
                            data: floor3Data,
                            borderColor: 'lavender',
                            borderWidth: 2,
                            fill: false
                        }, // ✅ Added comma
                        {
                            label: 'Setpoint',
                            data: floor3Setpoint, // This will create a horizontal line at 350
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
                                text: 'Height (mm)'
                            },
                            min: 0,
                            max: 1300
                        }
                    }
                }
            });

        </script>

    </div>

    <a href="../index.php">Go Back</a>
</body>
</html>
 
 
