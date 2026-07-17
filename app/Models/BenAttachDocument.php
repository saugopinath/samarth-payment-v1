<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class BenAttachDocument extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory, SoftDeletes;

    protected $connection = 'pgsql_enc';
    protected $table = 'jb_doc.ben_attach_documents';

    // The table uses a composite primary key in the DB, but Laravel Eloquent needs a single string primary key.
    // We can rely on the auto-incrementing 'id' column created by 'id bigserial' in the migration.

    protected $fillable = [
        'beneficiary_id',
        'scheme_id',
        'document_type',
        'attched_document',
        'created_by',
        'ip_address',
        'document_extension',
        'document_mime_type',
        'created_by_dist_code',
        'doc_type_name',
    ];
}
