<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Invitation to {{$invitation->event()->title}}</title>
</head>

<body style="font-family: 'Arial', sans-serif;">

	<div style="max-width: 300px; padding: 50px; margin: 0 auto; background-color: #f5f5f5; border-radius: 8px;">
		<div style="padding-bottom: 25px;">
			<h2 style="color: #444; text-align: center;">{{$invitation->event()->title}}</h2>
			<p style="color: #444; text-align: center;">{{$invitation->event()->description}}</p>
			<hr style="color: gray">
		</div>
		<div style="padding-bottom: 25px;">
			<p>Dear {{$invitation->guest->first_name}} {{$invitation->guest->last_name}},</p>
			<p>You are invited to {{$invitation->event()->title}}</p>
			<p>Venue: {{$invitation->event()->venue}}</p>
			<p>Date & Time: {{$invitation->event()->start}}</p>
		</div>
		<div style="text-align: center;">
			<img src="{{ $message->embed($qr_code_file, 'key.png') }}" width="300" height="300">
			<pre>Key: {{ $invitation->key }}</pre>
		</div>
	</div>
</body>

</html>