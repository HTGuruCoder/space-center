<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasCreator;
use App\Traits\HasWorkspaces;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Propaganistas\LaravelPhone\Casts\RawPhoneNumberCast;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, HasRoles, HasCreator, HasWorkspaces;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'picture_url',
        'face_token',
        'timezone',
        'birth_date',
        'country_code',
        'currency_code',
        'bank_name',
        'bank_account_number',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'face_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'phone_number' => RawPhoneNumberCast::class.':country_code',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user's initials.
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(
            mb_substr($this->first_name, 0, 1) . mb_substr($this->last_name, 0, 1)
        );
    }

    /**
     * Get the employee profile for this user.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Check if user has a complete employee profile.
     */
    public function hasCompleteEmployeeProfile(): bool
    {
        return $this->employee !== null;
    }

    /**
     * Get the users created by this user.
     */
    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * Check if user has facial recognition enabled.
     */
    public function hasFaceAuthEnabled(): bool
    {
        return !empty($this->face_token);
    }

    /**
     * Get temporary URL for user's profile picture.
     */
    public function getProfilePictureUrl(?int $expiresInMinutes = 60): ?string
    {
        if (empty($this->picture_url)) {
            return null;
        }

        // If stored in private disk, generate temporary URL
        if (\Storage::disk('private')->exists($this->picture_url)) {
            return \Storage::disk('private')->temporaryUrl(
                $this->picture_url,
                now()->addMinutes($expiresInMinutes)
            );
        }

        // If stored in public disk, return public URL
        if (\Storage::disk('public')->exists($this->picture_url)) {
            return \Storage::disk('public')->url($this->picture_url);
        }

        return null;
    }
}
