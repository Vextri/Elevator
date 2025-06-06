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
    if(isset($_POST['newfloor'])) {
        $curFlr = update_elevatorNetwork(1, $_POST['newfloor']); 
        header('Refresh:0; url=inside.php');	
        exit;
    } 
    $curFlr = get_currentFloor();
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inside Elevator</title>
    <link href="css/projectsVI.css" type="text/css" rel="stylesheet"/>
    <style>
        .elevator-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 2rem;
        }
        .arrow-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .arrow-btn {
            width: 60px;
            height: 60px;
            font-size: 2.5rem;
            border-radius: 50%;
            border: 2px solid #333;
            background: #e2e6ea;
            cursor: pointer;
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <div class="elevator-panel">
        <h2>Current floor: <span style="color:#007bff;"><?php echo $curFlr; ?></span></h2>
        <form action="inside.php" method="POST">
            <div class="arrow-buttons">
                <button type="submit" name="newfloor" value="<?php echo min(3, $curFlr+1); ?>" class="arrow-btn" <?php if($curFlr >= 3) echo 'disabled'; ?> title="Up">&#8593;</button>
                <button type="submit" name="newfloor" value="<?php echo max(1, $curFlr-1); ?>" class="arrow-btn" <?php if($curFlr <= 1) echo 'disabled'; ?> title="Down">&#8595;</button>
            </div>
        </form>
    </div>
</body>
</html>