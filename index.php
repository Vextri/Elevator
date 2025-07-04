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

    function setFloor_diagnostic(int $node_ID, int $new_floor =1): int {
        $db2 = new PDO('mysql:host=127.0.0.1;dbname=elevator','ese','ese');
        $query = 'INSERT INTO diagnostic (nodeID, currentFloor)
              VALUES (:id, :floor)
              ON DUPLICATE KEY UPDATE currentFloor = :floor';
    
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

    function diagnostics($node_ID, $curFlr) {
        $db = new PDO('mysql:host=127.0.0.1;dbname=elevator','ese','ese');
        $stmt = $db->prepare('SELECT distance FROM elevatorNetwork WHERE nodeID = :id AND currentFloor = :floor');
        $stmt->bindValue(':id', $node_ID, PDO::PARAM_INT);
        $stmt->bindValue(':floor', $curFlr, PDO::PARAM_INT);
        $stmt->execute();
        $distance = $stmt->fetchColumn();
        // Optionally update diagnostic table with distance here
        return $distance;
    }

?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/elevator.css" type="text/css" rel="stylesheet"/>
    <title>Elevator Controller GUI</title>
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

        if(isset($_POST['diagnostics'])) {
            
            $diagnosticArray = array_fill(0, 3, array_fill(0, 50, 0));
            for($i=1; $i<=10; $i++) 
            {
                $x = $i % 3;
                if ($x == 1) {
                    $curFlr = 1;
                } elseif ($x == 2) {
                    $curFlr = 2;
                } else {
                    $curFlr = 3;
                }
                update_elevatorNetwork(1, $curFlr);
                setFloor_diagnostic($i, $curFlr);
                sleep(2);
                $distance = diagnostics($i, $curFlr);

                $diagnosticArray[$curFlr-1][($i-1) % 50] = $distance;
                echo '<pre>';
                print_r($diagnosticArray[$curFlr-1][($i-1) % 50]); //double-check that it's updating
                echo '</pre>';

            }

            echo '<pre>';
            print_r($diagnosticArray); //double-check that it's updating
            echo '</pre>';
        }
    ?>

    <h2 class="floor-display">F <?php echo $curFlr; ?></h2>
    <form  class="elevator-panel"method="post" action="index.php">
        <div>
            <!-- UP arrow: should INCREASE floor -->
            <button type="submit" name="newfloor" value="<?php echo min(3, $curFlr+1); ?>" class="up" <?php if($curFlr >= 3) echo 'disabled'; ?> title="Up">&#8593;</button>
        </div>
        <div class="floor-row">
            <?php for($i=1; $i<=3; $i++): ?>
                <button type="submit" name="newfloor" value="<?php echo $i; ?>" class="floor"<?php if($curFlr == $i) echo ' active'; ?>><?php echo $i; ?></button>
            <?php endfor; ?>
        </div>
        <div>
            <!-- DOWN arrow: should DECREASE floor -->
            <button type="submit" name="newfloor" value="<?php echo max(1, $curFlr-1); ?>" class="down" <?php if($curFlr <= 1) echo 'disabled'; ?> title="Down">&#8595;</button>
        </div>
    </form>
    <form>
        <div>
            <button type="submit" name="diagnostics">Run Diagnostics</button>
        </div>
    </form>
</body>
</html>
 
 
