<?php

include_once 'creditcards.class.php';
$CCV = new CreditCardValidator();

if(isset($_POST['cardnum'])) :
	
	$CCV->Validate($_POST['cardnum']);
	$CARDINFO = $CCV->GetCardInfo();
	
endif;

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html lang="en">

	<head>
	
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

		<title>Credit Card Validation - Test Page</title>
		
		<style type="text/css">
			body{ font-size: 13px; color: #333; font-family: 'georgia', 'times new roman', serif; margin: 20px; }
			fieldset{ border: 0; margin: 0; font-style: italic; }
			legend{ display: none; }
			label{ width: 100%; float: left; clear: both; font-size: 15px; font-weight: bold; color: #999; line-height: 3; }
			input, textarea{ font-size: 18px; line-height: 1.4; padding: 10px; border: 2px solid #eee; }
			textarea{ background-color: #eee; color: blue; }
			h2{ font-size: 30px; }
			#fs-input input{ width: 500px; margin-bottom: 15px; }
			#fs-input input.cb{ width: auto; }
			#fs-submit input{ background-color: #333; color: lightyellow; }
		</style>

	</head>

	<body>

		<h2>Credit Card Validation Tester</h2>

		<form action="card-test.php" method="post">
			
			<fieldset id="fs-input">
				<legend></legend>
				<label>Card Number</label>
				<input type="text" name="cardnum" value="<?php echo @$_POST['cardnum']; ?>"><br>
				<input type="checkbox" name="showgeek" class="cb" value="1"<?php if(isset($_POST['showgeek'])) echo ' checked'; ?>> Show Geeky Output
			</fieldset>

			<fieldset id="fs-submit">
				<legend></legend>
				<label></label>
				<input type="submit" value="Validate Number">
			</fieldset>
			
			<?php if(isset($_POST['cardnum'])) : ?>
				<hr>
				<h2>Result</h2>
				<fieldset id="fs-result">
					<legend></legend>
					<label></label>
					<strong>Status:</strong> <?php echo strtoupper($CARDINFO['status']); ?><br>
					<strong>Card Type: </strong> <?php echo $CCV->GetCardName($CARDINFO['type']); ?>
				</fieldset>
			<?php endif; ?>
			<?php if(isset($_POST['showgeek'])) : ?>
				<fieldset id="fs-geek-result">
					<legend></legend>
					<label>Geeky Result</label>
					<textarea rows="40" cols="80"><?php print_r($CCV); ?></textarea>
				</fieldset>
			<?php endif; ?>
			
		</form>

	</body>

</html>

