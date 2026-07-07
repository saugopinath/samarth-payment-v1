<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterTab extends Model
{
  protected $fillable = [
    'tab_name',
    'tab_code',
    'tab_short_name',
    'tab_model_name',
    'tab_icon',
    'is_active',
  ];
  public function fields()
  {
    $model = match ($this->tab_code) {
      105 => SelfDeclerationBasefield::class,
      default => SchemeTabFormField::class,
    };

    return $this->hasMany(
      $model,
      'tab_code',
      'tab_code'
    );
  }
  public function getFields()
  {
    return $this->fields()
      ->orderBy('field_position')
      ->get();
  }
}
