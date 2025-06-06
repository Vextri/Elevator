<?php

    function update_elevatorNetwork(int $node_ID, int $new_floor =1): int {
        $db1 = new PDO('mysql:host=127.0.0.1;dbname=elevator','ese','ese');
        $query = 'UPDATE elevatorNetwork 
                SET currentFloor = :floor
                WHERE nodeID = :id';
        $statement = $db1->prepare($query);
        $statement->bindvalue('floor', $new_floor);
        $statement->bindvalue('id', $node_ID);
        $statement->execute();	
        
        return $new_floor;
    }

    function get_currentFloor(): int {
        $db = null;
        try {
            $db = new PDO('mysql:host=127.0.0.1;dbname=elevator','ese','ese');
        } catch (PDOException $e) {
            echo $e->getMessage();
            return 0;
        }
        if (!$db) return 0;

        $rows = $db->query('SELECT currentFloor FROM elevatorNetwork');
        foreach ($rows as $row) {
            $current_floor = $row[0];
        }
        return $current_floor ?? 0;
    }
?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/bootstrap.css" type="text/css" rel="stylesheet"/>
    <link href="css/projectsVI.css" type="text/css" rel="stylesheet"/>
    <link href="css/elevator.css" type="text/css" rel="stylesheet"/>
    <title>Elevator Controller GUI</title>
    <style>
        .elevator-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-top: 2rem;
        }
        .floor-buttons, .arrow-buttons {
            display: flex;
            justify-content: center;
            margin: 1rem 0;
        }
        .floor-btn, .arrow-btn {
            width: 60px;
            height: 60px;
            margin: 0 10px;
            font-size: 2rem;
            border-radius: 50%;
            border: 2px solid #333;
            background: #f8f9fa;
            cursor: pointer;
            transition: background 0.2s;
        }
        .floor-btn.active {
            background: #007bff;
            color: #fff;
            border-color: #007bff;
        }
        .arrow-btn {
            font-size: 2.5rem;
            background: #e2e6ea;
        }
    </style>
</head>
<body>
    <header>
        <h1 style="text-align:center;">Elevator Controls</h1>
    </header>

    <?php 
        if(isset($_POST['newfloor'])) {
            $curFlr = update_elevatorNetwork(1, $_POST['newfloor']); 
            header('Refresh:0; url=index.php');	
            exit;
        } 
        $curFlr = get_currentFloor();
    ?>

    <div class="elevator-panel">
        <h2>Current floor: <span style="color:#007bff;"><?php echo $curFlr; ?></span></h2>
        <form action="index.php" method="POST">
            <div class="arrow-buttons">
                <!-- UP arrow: should INCREASE floor -->
                <button type="submit" name="newfloor" value="<?php echo min(3, $curFlr+1); ?>" class="arrow-btn" <?php if($curFlr >= 3) echo 'disabled'; ?> title="Up">&#8593;</button>
            </div>
            <div class="floor-buttons">
                <?php for($i=1; $i<=3; $i++): ?>
                    <button type="submit" name="newfloor" value="<?php echo $i; ?>" class="floor-btn<?php if($curFlr == $i) echo ' active'; ?>"><?php echo $i; ?></button>
                <?php endfor; ?>
            </div>
            <div class="arrow-buttons">
                <!-- DOWN arrow: should DECREASE floor -->
                <button type="submit" name="newfloor" value="<?php echo max(1, $curFlr-1); ?>" class="arrow-btn" <?php if($curFlr <= 1) echo 'disabled'; ?> title="Down">&#8595;</button>
            </div>
        </form>
    </div>
</body>
</html>
 
 
