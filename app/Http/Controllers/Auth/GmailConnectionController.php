<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Mail\GmailOAuth;
use Illuminate\Http\Request;

class GmailConnectionController extends Controller
{
 public function __invoke(Request $request,GmailOAuth $oauth)
 {
  $headers=['Cache-Control'=>'no-store, private','Referrer-Policy'=>'no-referrer','X-Robots-Tag'=>'noindex, nofollow'];
  $message='Koneksi Gmail tidak berhasil. Buat tautan baru melalui console aplikasi dan pastikan akun pengirim sesuai.';
  if($request->has('error'))return response()->view('auth.gmail-connection',['connected'=>false,'message'=>$message],400,$headers);
  $data=$request->validate(['state'=>['required','string','size:64','regex:/^[a-zA-Z0-9]+$/'],'code'=>['required','string','max:4096']]);
  try{$oauth->complete($data['state'],$data['code']);}
  catch(\Throwable){return response()->view('auth.gmail-connection',['connected'=>false,'message'=>$message],400,$headers);}
  return response()->view('auth.gmail-connection',['connected'=>true,'message'=>'Gmail pengirim HSNCO sudah terhubung. Anda dapat menutup halaman ini dan menguji pengiriman dari console aplikasi.'],200,$headers);
 }
}