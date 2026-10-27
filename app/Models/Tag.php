<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = array('name');

    protected $hidden = array('pivot');

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tag');
    }
}
