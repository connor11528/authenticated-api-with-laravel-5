<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
	/**
	* Validation rules
	*
	* @param bool $forUpdate
	* @return array
	*/
	public function getValidationRules($forUpdate = false)
	{
		$createRule = [
	        'title' => 'required|max:200',
	        'description' => 'required|min:10',
	        'allow_comments' => 'boolean',
	        'url' => 'required|url',
	        'thumbnail' => 'required|url',
	        'channel_id' => 'required|integer'
	    ];

	    $updateRule = [
	        'title' => 'max:200',
	        'description' => 'min:10',
	        'url' => 'url',
	        'thumbnail' => 'url'
	    ];

	    return $forUpdate ? $updateRule : $createRule;
	}

	public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->select([
            'id', 'name', 'avatar', 'created_at'
        ]);
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
