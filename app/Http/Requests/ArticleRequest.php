<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body_web' => 'nullable|array',
            'excerpt' => 'nullable|string|max:500',
            'thumbnail' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,avif,heic,heif',
            'caption_ig' => 'nullable|string|max:2200',
            'hashtags_ig' => 'nullable|string|max:1000',
            'status' => 'nullable|in:draft,submitted,returned,approved,published',
            'target_platform' => 'nullable|in:web,ig,both',
            'category_id' => 'nullable|exists:categories,id',
            'opd_id' => 'nullable|exists:opds,id',
            'published_at' => 'nullable|date',
            'ig_type' => 'nullable|in:feed,reels',
            'ig_media' => 'nullable|array',
            'ig_media.*' => 'file|mimes:jpg,jpeg,png,gif,webp,avif,heic,heif,mp4,mov,webm,ogg',
            'ig_media_delete_ids' => 'nullable|array',
            'ig_media_delete_ids.*' => 'integer|exists:media,id',
            'supporting_files' => 'nullable|array',
            'supporting_files.*' => 'file|mimes:jpg,jpeg,png,gif,webp,avif,heic,heif,mp4,mov,webm,ogg,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,txt',
            'supporting_file_delete_ids' => 'nullable|array',
            'supporting_file_delete_ids.*' => 'integer|exists:media,id',
            'supporting_descriptions' => 'nullable|array',
            'supporting_descriptions.*' => 'nullable|string|max:255',
            'upload_proofs' => 'nullable|array',
            'upload_proofs.*' => 'nullable|string|max:255',
            'editor_id' => 'nullable|exists:users,id',
            // SEO fields
            'seo.seo_title' => 'nullable|string|max:255',
            'seo.seo_description' => 'nullable|string|max:160',
            'seo.seo_keywords' => 'nullable|string|max:500',
            'seo.og_title' => 'nullable|string|max:255',
            'seo.og_description' => 'nullable|string|max:255',
            'seo.og_image' => 'nullable|string|max:255',
            'seo.canonical_url' => 'nullable|url|max:255',
            'seo.robots_index' => 'nullable|boolean',
            'seo.robots_follow' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        $uploadMax = (string) ini_get('upload_max_filesize');

        return [
            'thumbnail.uploaded' => "Thumbnail gagal diunggah. Ukuran file melebihi batas upload server aktif ({$uploadMax}) atau koneksi terputus. Pilih ulang file thumbnail lalu simpan kembali.",
            'thumbnail.mimes' => 'Format thumbnail tidak didukung. Gunakan jpg, jpeg, png, gif, webp, avif, heic, atau heif.',
            'ig_media.*.uploaded' => "Media Instagram gagal diunggah. Ukuran file melebihi batas upload server aktif ({$uploadMax}) atau koneksi terputus. Pilih ulang file lalu simpan kembali.",
            'supporting_files.*.uploaded' => "File pendukung gagal diunggah. Ukuran file melebihi batas upload server aktif ({$uploadMax}) atau koneksi terputus. Pilih ulang file lalu simpan kembali.",
        ];
    }
}
