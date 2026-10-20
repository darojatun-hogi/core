<!DOCTYPE html>
<html>

	<head>
		<meta charset="utf-8">
		<style>
			body {
				font-family: sans-serif;
				background: #f4f4f4;
				padding: 30px;
				margin: 0;
			}

			.card {
				background: #fff;
				border-radius: 10px;
				padding: 36px;
				max-width: 520px;
				margin: auto;
				box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
			}

			h1 {
				color: #4f46e5;
				font-size: 22px;
				margin-bottom: 8px;
			}

			p {
				color: #374151;
				line-height: 1.7;
				font-size: 15px;
			}

			.btn {
				display: inline-block;
				background: transparent;
				color: #4f46e5 !important;
				text-decoration: none !important;
				padding: 12px 28px;
				border-radius: 8px;
				border: 2px solid #4f46e5;
				font-weight: 600;
				margin: 20px 0;
			}

			.note {
				color: #9ca3af;
				font-size: 12px;
				margin-top: 24px;
				border-top: 1px solid #f0f0f0;
				padding-top: 16px;
			}
		</style>
	</head>

	<body>
		<div class="card">
			<h1>Verifikasi Email Kamu</h1>
			<p>Halo, <strong>{{ $user->name }}</strong>!</p>
			<p>Terima kasih telah mendaftar di <strong>Elips</strong>. Klik tombol di bawah untuk memverifikasi alamat
				email kamu.</p>

			<a href="{{ $url }}" class="btn">Verifikasi Email Sekarang</a>

			<p>Link ini akan kedaluwarsa dalam <strong>60 menit</strong>.</p>
			<p>Jika kamu tidak merasa mendaftar, abaikan email ini.</p>

			<div class="note">
				Jika tombol tidak bisa diklik, salin URL ini ke browser:<br>
				<span style="color:#4f46e5; word-break:break-all;">{{ $url }}</span>
			</div>
		</div>
	</body>

</html>
