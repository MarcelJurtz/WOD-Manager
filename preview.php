
<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="stylesheet" href="./assets/css/stylesheet.css" />
	<title>OneFill</title>
</head>

<body>
<div class="container-sm">
	<form>
		<h1>OneFill Vorschau</h1>
		
		<div class="form-group">
			<input type="text" name="name" id="name" class="form-field" placeholder="Name">
			<label for="name" class="form-label">Name</label>
		</div>

		<div class="form-group">
			<input type="text" name="token" id="token" class="form-field" placeholder="API Token">
			<label for="token" class="form-label">API Token</label>
		</div>
		
		<div class="row mt-5">
			<div class="col-md-12">
				<button type="button" id="btn-generate" class="btn">Erstellen</button>
			</div>
		</div>
		<div class="row"><p id="api-url-info"></p></div>
		<div class="row"><p id="api-url-info-decrypted"></p></div>
	</form>

</div>

<script	src="./assets/js/jquery-3.6.0.min.js"></script>
<script src="./assets/js/crypto-js/aes.js"></script>
<script src="./assets/js/scripts.js"></script>
</body>

</html>