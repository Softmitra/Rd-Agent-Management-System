<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Agent extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_id',
        'name',
        'email',
        'mobile_number',
        'password',
        'aadhar_number',
        'pan_number',
        'aadhar_file',
        'pan_file',
        'contact_info',
        'branch',
        'is_verified',
        'is_active',
        'account_expires_at',
        'verification_remarks'
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
        'verified_at' => 'datetime',
        'account_expires_at' => 'datetime',
        'contact_info' => 'json',
        'password' => 'hashed',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the verifier of this agent.
     */
    public function verifier()
    {
        return $this->belongsTo(Agent::class, 'verified_by');
    }

    /**
     * Get the roles that belong to the agent.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'agent_role');
    }

    /**
     * Check if the agent has a specific role.
     *
     * @param string|array $roles
     * @return bool
     */
    public function hasRole($roles): bool
    {
        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->roles->contains('name', $role)) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    /**
     * Assign a role to the agent.
     *
     * @param string|Role $role
     * @return void
     */
    public function assignRole($role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        
        if (!$this->hasRole($role->name)) {
            $this->roles()->attach($role->id);
        }
    }

    /**
     * Remove a role from the agent.
     *
     * @param string|Role $role
     * @return void
     */
    public function removeRole($role): void
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->firstOrFail();
        }
        
        $this->roles()->detach($role->id);
    }

    /**
     * Verify the agent.
     *
     * @param Agent $verifier
     * @param string|null $remarks
     * @return bool
     */
    public function verify(Agent $verifier, ?string $remarks = null): bool
    {
        return $this->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $verifier->id,
            'verification_remarks' => $remarks,
        ]);
    }

    /**
     * Unverify the agent.
     *
     * @param string|null $remarks
     * @return bool
     */
    public function unverify(?string $remarks = null): bool
    {
        return $this->update([
            'is_verified' => false,
            'verified_at' => null,
            'verified_by' => null,
            'verification_remarks' => $remarks,
        ]);
    }

    /**
     * Activate the agent account.
     *
     * @param \DateTime|null $expiresAt
     * @return bool
     */
    public function activate(?\DateTime $expiresAt = null): bool
    {
        return $this->update([
            'is_active' => true,
            'account_expires_at' => $expiresAt,
        ]);
    }

    /**
     * Deactivate the agent account.
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        return $this->update([
            'is_active' => false,
        ]);
    }

    /**
     * Check if the agent account is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if (!$this->account_expires_at) {
            return false;
        }

        return $this->account_expires_at->isPast();
    }

    /**
     * Check if the agent can access the system.
     *
     * @return bool
     */
    public function canAccess(): bool
    {
        return $this->is_verified && $this->is_active && !$this->isExpired();
    }

    /**
     * Get the customers assigned to this agent.
     */
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get the RD accounts managed by this agent.
     */
    public function rdAccounts()
    {
        return $this->hasMany(RDAccount::class);
    }

    /**
     * Get the payments collected by this agent.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
