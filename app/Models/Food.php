<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Inline\Element\Strong;

class Food extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description','ingredients','price', 'rate', 'types','picturePath'
    ];

    public function getCreatedAtAttribute($value) //untuk merubah crated at ke unix timestamp
    {
        return  Carbon::parse($value)->timestamp;
    }
    public function getUpdateAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function toArray()
    {
        $toArray = parent::toArray();
        $toArray['picturePath'] = $this->picturePath;
        return $toArray;
    }

    public function getPicturePathAttribute()
    {
        return url('').Storage::url($this->attributes['picturePath']);
    }

}
