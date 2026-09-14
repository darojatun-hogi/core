<!DOCTYPE html>
<html lang="id">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>@yield('title', 'Elips')</title>

		@vite(['resources/css/app.css', 'resources/js/app.js'])
	</head>

	<body class="h-screen overflow-hidden">

		<div class="flex h-full">
			@include('layouts.dashboard.sidebar')
			<div class="flex flex-col flex-1 min-w-0 overflow-y-auto">

				@include('layouts.dashboard.navbar')

				<main class="px-3 py-4 flex-1">
					@yield('content')
				</main>

			</div>
		</div>

	</body>

</html>
