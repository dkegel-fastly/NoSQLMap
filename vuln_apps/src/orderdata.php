<!DOCTYPE html>
<html>
	<head>
		<title>Order Lookup</title>
	</head>
	<body>
		<?php
		if (isset($_GET['ordersearch']) && !empty($_GET['ordersearch'])) {
			try {
				$result = "";
				$manager = new MongoDB\Driver\Manager('mongodb://root:prisma@mongo:27017');
				$search = $_GET['ordersearch'];
				$js = "function () { var query = '". $search . "'; return this.id == query;}";
				//print $js;
				print '<br/>';

				// Create query with $where operator for JavaScript injection vulnerability
				$filter = ['$where' => $js];
				$options = [];
				$query = new MongoDB\Driver\Query($filter, $options);

				$cursor = $manager->executeQuery('shop.orders', $query);
				$docs = $cursor->toArray();
				echo count($docs) . ' order(s) found. <br/>';

				foreach ($docs as $obj) {
						echo 'Order ID: ' . $obj->id . '<br/>';
						echo 'Name: ' . $obj->name . '<br/>';
						echo 'Item: ' . $obj->item . '<br/>';
						echo 'Quantity: ' . $obj->quantity. '<br/>';
						echo '<br/>';
				}
			} catch (MongoDB\Driver\Exception\Exception $e) {
				die('Error connecting to MongoDB server : ' . $e->getMessage());
			} catch (Exception $e) {
				die('Error: ' . $e->getMessage());
			}
		}
		?>


		<b>Use the Order ID to locate your order:</b><br>
		<form method="get" id="usersearch">
			<p>Search <input type="text" name="ordersearch" id="ordersearch" /> <input type="submit" name="submitbutton"
					value="Submit" /></p>
		</form>
		<div id="results">
			<?php echo $result; ?>
		</div>
	</body>

</html>
