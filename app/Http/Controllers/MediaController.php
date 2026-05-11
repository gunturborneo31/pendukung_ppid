<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,webm,ogg,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,txt|max:51200',
            'article_id' => 'required|exists:articles,id',
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $mime = (string) $file->getMimeType();
        $type = str_starts_with($mime, 'video/') ? 'video' : 'image';
        $path = $file->store('media', 'public');

        $media = Media::create([
            'article_id' => $request->article_id,
            'uploader_id' => auth()->id(),
            'type' => $type,
            'path' => $path,
            'alt_text' => $request->alt_text,
            'size' => $file->getSize(),
            'description' => $request->description,
        ]);

        return response()->json([
            'id' => $media->id,
            'url' => Storage::url($path),
            'type' => $type,
            'alt_text' => $media->alt_text,
            'description' => $media->description,
        ]);
    }

    public function destroy(Media $media)
    {
        Storage::disk('public')->delete($media->path);
        $media->delete();

        return response()->json(['message' => 'Media deleted.']);
    }
}
