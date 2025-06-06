
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
        header('Refresh:0; url=outside.php');	
        exit;
    } 
    $curFlr = get_currentFloor();
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Outside Elevator</title>
    <link href="css/projectsVI.css" type="text/css" rel="stylesheet"/>
    <style>
        .elevator-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 2rem;
        }
        .panel-vertical {
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 320px; /* Adjust as needed */
            justify-content: center;
            position: relative;
        }
        .floor-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .door-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 0;
        }
        .spacer {
            height: 70px; /* Adjust this value to align with the "2" button */
        }
        .floor-btn {
            width: 60px;
            height: 60px;
            font-size: 2rem;
            border-radius: 50%;
            border: 2px solid #333;
            background: #f8f9fa;
            cursor: pointer;
            margin: 0.5rem 0;
            transition: background 0.2s, color 0.2s, border-color 0.2s;
        }
        .floor-btn.active {
            background: #007bff;
            color: #fff;
            border-color: #007bff;
        }
        .door-btn.selected {
            background: #28a745;
            color: #fff;
            border-color: #28a745;
        }
    </style>
</head>
<body>
 <div class="elevator-panel">
        <h2>Current floor: <span style="color:#007bff;"><?php echo $curFlr; ?></span></h2>
        <form action="outside.php" method="POST">
            <div class="panel-vertical">
                <div class="floor-buttons">
                    <?php for($i=3; $i>=1; $i--): ?>
                        <button type="submit" name="newfloor" value="<?php echo $i; ?>" class="floor-btn<?php if($curFlr == $i) echo ' active'; ?>"><?php echo $i; ?></button>
                    <?php endfor; ?>
                </div>
                <div class="spacer"></div>
                <div class="door-buttons">
                    <button type="button" class="floor-btn door-btn" id="open-btn" title="Open Door">&lt;&gt;</button>
                    <button type="button" class="floor-btn door-btn" id="close-btn" title="Close Door">&gt;&lt;</button>
                </div>
            </div>
        </form>
    </div>
        <script>
        // Highlight the clicked door button for 5 seconds, then remove highlight
        const openBtn = document.getElementById('open-btn');
        const closeBtn = document.getElementById('close-btn');
        let doorBtnTimeout = null;

        function highlightDoor(btnToHighlight, btnToUnhighlight) {
            btnToHighlight.classList.add('selected');
            btnToUnhighlight.classList.remove('selected');
            if (doorBtnTimeout) clearTimeout(doorBtnTimeout);
            doorBtnTimeout = setTimeout(() => {
                btnToHighlight.classList.remove('selected');
            }, 5000);
        }

        openBtn.addEventListener('click', function() {
            highlightDoor(openBtn, closeBtn);
        });

        closeBtn.addEventListener('click', function() {
            highlightDoor(closeBtn, openBtn);
        });
    </script>
</body>
</html>