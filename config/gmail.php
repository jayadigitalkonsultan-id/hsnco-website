<?php
return [
 'client_id'=>env('GMAIL_CLIENT_ID'),
 'client_secret'=>env('GMAIL_CLIENT_SECRET'),
 'sender'=>env('GMAIL_SENDER_EMAIL','jaya.digitalkonsultan@gmail.com'),
 'redirect_uri'=>env('GMAIL_REDIRECT_URI'),
 'state_minutes'=>10,
];