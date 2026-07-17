<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTypeMaster extends Model
{
    use HasFactory;

    protected $table = 'document_type_masters';

    protected $fillable = [
        'document_type_code',
        'document_mime_type',
        'document_extension',
        'max_size',
    ];

    protected $casts = [
        'document_mime_type' => 'array',
        'document_extension' => 'array',
        'max_size' => 'integer',
    ];

    /**
     * Get the codemaster entry associated with the document type.
     */
    public function codemaster()
    {
        return $this->belongsTo(Codemaster::class, 'document_type_code', 'code');
    }
}
