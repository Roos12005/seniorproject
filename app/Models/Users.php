<?php

namespace App\Models;

use Vinelab\NeoEloquent\Eloquent\Model as NeoEloquent;

class Users extends NeoEloquent
{
	protected $table = 'User';
}