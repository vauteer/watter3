<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function profileURL(): string
    {
//        if ($this->profile_image) {
//            return asset('storage/profile/' . $this->profile_image);
//        } else {

        return "https://www.gravatar.com/avatar/" .
            md5(strtolower(trim($this->email))) .
            "?d=mp&s=40";
    }

    public static function profilePath(string $stub = ''): string
    {
        return storage_path('app/public/profile') .
            DIRECTORY_SEPARATOR .
            trim($stub, DIRECTORY_SEPARATOR);
    }

    public static function removeOrphanProfileImages(): int
    {
        $count = 0;
        foreach (glob(self::profilePath('*')) as $filename) {
            $user = User::where('profile_image', basename($filename))->first();
            if ($user === null) {
                unlink($filename);
                $count++;
            }
        }

        return $count;
    }
}
