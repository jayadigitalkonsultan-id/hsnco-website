<?php
namespace App\Services\Admin;
use App\Models\Client;
use App\Models\Media;
use App\Models\ContentModel;
use App\Models\User;
use App\Services\SecurityAudit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class ClientLogoUpload
{
    public function save(Client $client, array $data, User $actor, DraftContentService $drafts): ContentModel
    {
        $file = $data['logo_file'];
        unset($data['logo_file']);
        Gate::forUser($actor)->authorize('create', Media::class);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());
        $extensions = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        $size = @getimagesize($file->getRealPath());
        if (!isset($extensions[$mime]) || !$size || ($size['mime'] ?? null) !== $mime) {
            throw ValidationException::withMessages(['logo_file'=>'Logo harus berupa gambar JPEG, PNG, atau WebP yang valid.']);
        }
        $path = $file->storeAs('media', Str::ulid().'.'.$extensions[$mime], 'public');
        if (!$path) throw ValidationException::withMessages(['logo_file'=>'Logo belum dapat disimpan. Silakan coba lagi.']);
        try {
            return DB::transaction(function () use ($client,$data,$actor,$drafts,$file,$path,$mime,$size) {
                $media = Media::create(['disk'=>'public','path'=>$path,'original_name'=>basename($file->getClientOriginalName()),'mime_type'=>$mime,'size'=>$file->getSize(),'width'=>$size[0],'height'=>$size[1],'alt_text'=>'Logo '.$data['name'],'visibility'=>'public','uploaded_by'=>$actor->id]);
                $data['image_id'] = $media->id;
                $saved = $drafts->save($client,$data,$actor);
                app(SecurityAudit::class)->record('media_uploaded','success',$actor,request(),null,['visibility'=>'public','mime_type'=>$mime],$media);
                return $saved;
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }
    }
}
