<!DOCTYPE html>
<html lang="id">

	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Login</title>

		@vite(['resources/css/app.css', 'resources/js/app.js'])
	</head>

	<body class="font-sans antialiased bg-base-200">
		<div class="flex min-h-screen items-center justify-center px-4">
			<div class="card w-full max-w-md bg-base-100 shadow-md">
				<div class="card-body">
					@yield('content')
				</div>
			</div>
		</div>
	</body>

</html>
