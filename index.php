<?php
	function update_elevatorNetwork(int $node_ID, int $new_floor =1): int {
		$db1 = new PDO('mysql:host=127.0.0.1;dbname=elevator','Blaise','Gitdead32!32');
		$query = 'UPDATE elevatorNetwork 
				SET currentFloor = :floor
				WHERE nodeID = :id';
		$statement = $db1->prepare($query);
		$statement->bindvalue('floor', $new_floor);
		$statement->bindvalue('id', $node_ID);
		$statement->execute();	
		
		return $new_floor;
	}
?>
<?php 
   function get_currentFloor(): int {
        $db = null;
        try {
            $db = new PDO('mysql:host=127.0.0.1;dbname=elevator','Blaise','Gitdead32!32');
        } catch (PDOException $e) {
            echo $e->getMessage();
            return 0; // Return 0 or handle error as needed
        }
        if (!$db) return 0;

        // Query the database to display current floor
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
		<link href="projectsVI.css" type="text/css" rel="stylesheet"/>
		
		<title>Elevator Controller GUI</title>
	</head>

	<body>
		<header>
    		<h1>Elevator Controls</h1>
		</header>

		<h1>Project VI</h1>
		<h1>ESE 2026</h1>
		<h2>Amy Wentzell, Blaise Swan</h2> 
	
		<div>
			<form action="index.php" method="POST" style="display:inline;">
				<input type="hidden" name="newfloor" value="<?php echo min(3, get_currentFloor() + 1); ?>">
				<button type="submit" class="up">UP</button>
			</form>
			<form action="index.php" method="POST" style="display:inline;">
				<input type="hidden" name="newfloor" value="<?php echo max(1, get_currentFloor() - 1); ?>">
				<button type="submit" class="down">DOWN</button>
			</form>
		</div>

		<div>
			<form action="index.php" method="POST" style="display:inline;">
				<input type="hidden" name="newfloor" value="1">
				<button type="submit" class="floor">1</button>
			</form>
			<form action="index.php" method="POST" style="display:inline;">
				<input type="hidden" name="newfloor" value="2">
				<button type="submit" class="floor">2</button>
			</form>
			<form action="index.php" method="POST" style="display:inline;">
				<input type="hidden" name="newfloor" value="3">
				<button type="submit" class="floor">3</button>
			</form>
		</div>
		
		<?php 
			if(isset($_POST['newfloor'])) {
				$curFlr = update_elevatorNetwork(1, $_POST['newfloor']); 
				header('Refresh:0; url=index.php');	
			} 
			$curFlr = get_currentFloor();
			echo "<h2>Current floor # $curFlr </h2>";			
		?>		
		
		<h2> 	
			<form action="index.php" method="POST">
				Request floor # <input type="number" style="width:50px; height:40px" name="newfloor" max=3 min=1 required />
				<input type="submit" value="Go"/>
			</form>
		</h2>	
	</body>
</html>
 
 
