<?php
    session_start();

    if (!isset($_SESSION['nodeID'])) 
    {
        $_SESSION['nodeID'] = 1;
    } else {
        $_SESSION['nodeID']++;
        $_SESSION['nodeID'] = ($_SESSION['nodeID'] % 90) + 1; // Cycle through node IDs 1 to 90
    }
    
    function update_elevatorNetwork(int $node_ID, int $new_floor =1): int {
        $db1 = new PDO('mysql:host=127.0.0.1;dbname=elevator','ese','ese');
        $query = '
            INSERT INTO elevatorNetwork (nodeID, requestedFloor)
            VALUES (:id, :floor)
            ON DUPLICATE KEY UPDATE requestedFloor = :floor';
        $statement = $db1->prepare($query);
        $statement->bindvalue('floor', $new_floor);
        $statement->bindvalue('id', $node_ID);
        $statement->execute();	
        
        return $new_floor;
    }

    function get_currentFloor(): int {
        try {
            $db = new PDO('mysql:host=127.0.0.1;dbname=elevator','ese','ese');
        } catch (PDOException $e) {
            echo $e->getMessage();
            return 1; // Fallback floor
        }

        $query = 'SELECT currentFloor FROM elevatorNetwork ORDER BY nodeID ASC LIMIT 1';
        $stmt = $db->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (int)$result['currentFloor'] : 1;
    }

    /*function get_currentFloor(): int {
		try { $db = new PDO('mysql:host=127.0.0.1;dbname=elevator','ese','ese');}
		catch (PDOException $e){echo $e->getMessage();}

			// Query the database to display current floor
			$rows = $db->query('SELECT currentFloor FROM elevatorNetwork');
			foreach ($rows as $row) {
				$curFlr = $row[0];
			}
			return $curFlr;
	}*/
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
        $curFlr = get_currentFloor(); // Get current floor from database
        if(isset($_POST['newfloor'])) {
            $curFlr = update_elevatorNetwork($_SESSION['nodeID'], $_POST['newfloor']); 

            header('Refresh:0; url=index.php');	
            exit;
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
    <a href="diagnostics/diagnostics.php">Show Diagnostics</a>
</body>
</html>
 
 
