<!DOCTYPE html>
<html>
	<head>
		<title>User Profile Lookup</title>
	</head>
	<body>
		<?php
		if (isset($_GET['usersearch']) && !empty($_GET['usersearch'])) {
			try {
			$result = "";
			$manager = new MongoDB\Driver\Manager('mongodb://root:prisma@mongo:27017');
			$usersearch = $_GET['usersearch'];
			$js = "function () { var query = '". $usersearch . "'; return this.username == query;}";
			print $js;
			print '<br/>';

			// Create query with $where operator for JavaScript injection vulnerability
			$filter = ['$where' => $js];
			$options = [];
			$query = new MongoDB\Driver\Query($filter, $options);

			$cursor = $manager->executeQuery('appUserData.users', $query);
			$docs = $cursor->toArray();
			echo count($docs) . ' user found. <br/>';

			foreach ($docs as $obj) {
					echo 'Name: ' . $obj->name . '<br/>';
					echo 'Username: ' . $obj->username . '<br/>';
					echo 'Email: ' . $obj->email . '<br/>';
					echo '<br/>';
			}

	} catch (MongoDB\Driver\Exception\Exception $e) {
		die('Error connecting to MongoDB server : ' . $e->getMessage());
	} catch (Exception $e) {
		die('Error: ' . $e->getMessage());
	}
	}
	?>


		<b>Enter your username:</b><br>
		<form method="get" id="usersearch">
			<p>Search <input type="text" name="usersearch" id="usersearch" /> <input type="submit" name="submitbutton"
					value="Submit" /></p>
		</form>
		<div id="results">
			<?php echo $result; ?>
		</div>
	</body>
</html>
